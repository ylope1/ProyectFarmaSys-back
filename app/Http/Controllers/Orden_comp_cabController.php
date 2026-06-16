<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden_comp_cab;
use App\Models\Orden_comp_det;
use App\Models\Presup_comp_cab;
use App\Models\Presup_comp_det;
use App\Traits\VerificaPermisos;
use Illuminate\Support\Facades\DB;

class Orden_comp_cabController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'movimientos/compras/orden_compras/';

    public function read() { 
        
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("
            select 
                occ.*,
                to_char(occ.orden_comp_fec, 'dd/mm/yyyy HH24:mi:ss') as orden_comp_fec,
                to_char(occ.orden_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') as orden_comp_fec_aprob,
                p.proveedor_desc,
                e.empresa_desc,
                s.suc_desc,
                u.name,
                tf.tipo_fact_desc,
                'PEDIDO NRO:' || to_char(occ.pedido_comp_id, '0000000') || 
                ' FECHA PEDIDO: ' || to_char(ped.pedido_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || 
                ' (' || ped.pedido_comp_estado || ')' as pedido,
                'PRESUPUESTO NRO:' || to_char(occ.presup_comp_id, '0000000') || 
                ' FECHA PRESUPUESTO: ' || coalesce(to_char(pre.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss'), '') || 
                ' (' || pre.presup_comp_estado || ')' as presupuesto
            from orden_comp_cab occ
            join proveedores p on p.id = occ.proveedor_id
            join empresas e on e.id = occ.empresa_id 
            join sucursales s on s.id = occ.sucursal_id
            join users u on u.id = occ.user_id 
            join tipo_fact tf on tf.id = occ.tipo_fact_id 
            join presup_comp_cab pre on pre.id = occ.presup_comp_id
            join pedidos_comp_cab ped on ped.id = occ.pedido_comp_id
            order by occ.id desc
        ");
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'mensaje' => 'Usuario no autenticado',
                'tipo' => 'error'
            ], 401);
        }

        $datosValidados = $request->validate([
            'presup_comp_id' => 'required|exists:presup_comp_cab,id',
            'tipo_fact_id' => 'required|exists:tipo_fact,id',
            'orden_comp_fec' => 'required',
            'orden_comp_ifv' => 'required',
        ]);

        $funcionario = DB::table('funcionarios')
            ->where('user_id', $user->id)
            ->first();

        if (!$funcionario) {
            return response()->json([
                'mensaje' => 'El usuario autenticado no tiene un funcionario asociado',
                'tipo' => 'error'
            ], 400);
        }

        if (!$funcionario->empresa_id || !$funcionario->sucursal_id) {
            return response()->json([
                'mensaje' => 'El funcionario no tiene empresa o sucursal asignada',
                'tipo' => 'error'
            ], 400);
        }

        $presup_comp_cab = Presup_comp_cab::find($request->presup_comp_id);

        if (!$presup_comp_cab) {
            return response()->json([
                'mensaje' => 'Presupuesto de compra no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($presup_comp_cab->presup_comp_estado != 'APROBADO') {
            return response()->json([
                'mensaje' => 'Solo se puede generar orden de compra desde un presupuesto APROBADO',
                'tipo' => 'error'
            ], 400);
        }

        $tieneDetalle = DB::table('presup_comp_det')
            ->where('presup_comp_id', $request->presup_comp_id)
            ->exists();

        if (!$tieneDetalle) {
            return response()->json([
                'mensaje' => 'No se puede generar orden de compra desde un presupuesto sin detalles',
                'tipo' => 'error'
            ], 400);
        }

        $existeOrden = Orden_comp_cab::where('presup_comp_id', $request->presup_comp_id)
            ->whereIn('orden_comp_estado', ['PENDIENTE', 'CONFIRMADO', 'APROBADO'])
            ->first();

        if ($existeOrden) {
            return response()->json([
                'mensaje' => 'Ya existe una orden de compra activa para este presupuesto',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $orden_comp_cab = Orden_comp_cab::create([
                'presup_comp_id' => $request->presup_comp_id,
                'proveedor_id' => $presup_comp_cab->proveedor_id,
                'user_id' => $user->id,
                'sucursal_id' => $funcionario->sucursal_id,
                'empresa_id' => $funcionario->empresa_id,
                'pedido_comp_id' => $presup_comp_cab->pedido_comp_id,
                'tipo_fact_id' => $request->tipo_fact_id,
                'orden_comp_fec' => $request->orden_comp_fec,
                'orden_comp_fec_aprob' => null,
                'orden_comp_ifv' => $request->orden_comp_ifv,
                'orden_comp_estado' => 'PENDIENTE'
            ]);

            $presup_comp_det = DB::select("
                select 
                    pcd.producto_id,
                    pcd.presup_comp_cant,
                    pcd.presup_comp_costo
                from presup_comp_det pcd
                where pcd.presup_comp_id = ?
            ", [$request->presup_comp_id]);

            foreach ($presup_comp_det as $dp) {
                $orden_comp_det = new Orden_comp_det();
                $orden_comp_det->orden_comp_id = $orden_comp_cab->id;
                $orden_comp_det->producto_id = $dp->producto_id;
                $orden_comp_det->orden_comp_cant = $dp->presup_comp_cant;
                $orden_comp_det->orden_comp_costo = $dp->presup_comp_costo;
                $orden_comp_det->save();
            }

            $presup_comp_cab->presup_comp_estado = 'PROCESADO';
            $presup_comp_cab->user_id = $user->id;
            $presup_comp_cab->save();

            DB::commit();

            return response()->json([
                'mensaje' => 'Registro creado con éxito',
                'tipo' => 'success',
                'registro' => $orden_comp_cab
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al registrar la orden de compra: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }

    public function update(Request $request, $id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $orden_comp_cab = Orden_comp_cab::find($id);

        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }

        if ($orden_comp_cab->orden_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede modificar una orden de compra pendiente',
                'tipo' => 'error'
            ], 400);
        }

        $datosValidados = $request->validate([
            'tipo_fact_id' => 'required|exists:tipo_fact,id',
            'orden_comp_fec' => 'required|date',
            'orden_comp_ifv' => 'required'
        ]);

        $datosValidados['user_id'] = auth()->user()->id;

        $orden_comp_cab->update($datosValidados);
        
        $orden_comp_cab->update($datosValidados);

        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $orden_comp_cab
        ],200);
    }

    public function anular(Request $request, $id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular');
        if ($permiso) {
            return $permiso;
        }

        $orden_comp_cab = Orden_comp_cab::find($id);

        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }

        if ($orden_comp_cab->orden_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden anular órdenes de compra en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $orden_comp_cab->orden_comp_estado = 'ANULADO';
            $orden_comp_cab->user_id = auth()->user()->id;
            $orden_comp_cab->save();

            $presup_comp_cab = Presup_comp_cab::find($orden_comp_cab->presup_comp_id);

            if ($presup_comp_cab) {
                $presup_comp_cab->presup_comp_estado = 'APROBADO';
                $presup_comp_cab->user_id = auth()->user()->id;
                $presup_comp_cab->save();
            }

            DB::commit();

            return response()->json([
                'mensaje'=> 'Registro anulado con exito',
                'tipo'=>'success',
                'registro'=> $orden_comp_cab
            ],200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al anular la orden de compra: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }

    public function confirmar(Request $request, $id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'confirmar');
        if ($permiso) {
            return $permiso;
        }

        $orden_comp_cab = Orden_comp_cab::find($id);

        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }

        if ($orden_comp_cab->orden_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar órdenes de compra en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        if (
            empty($orden_comp_cab->user_id) ||
            empty($orden_comp_cab->proveedor_id) ||
            empty($orden_comp_cab->sucursal_id) ||
            empty($orden_comp_cab->empresa_id) ||
            empty($orden_comp_cab->presup_comp_id) ||
            empty($orden_comp_cab->pedido_comp_id) ||
            empty($orden_comp_cab->tipo_fact_id) ||
            empty($orden_comp_cab->orden_comp_fec)
        ) {
            return response()->json([
                'mensaje' => 'La cabecera de la orden de compra contiene datos incompletos',
                'tipo' => 'error'
            ], 400);
        }

        $tieneDetalle = DB::table('orden_comp_det')
            ->where('orden_comp_id', $id)
            ->exists();

        if (!$tieneDetalle) {
            return response()->json([
                'mensaje' => 'No se puede confirmar una orden de compra sin detalles',
                'tipo' => 'error'
            ], 400);
        }

        $orden_comp_cab->orden_comp_estado = 'CONFIRMADO';
        $orden_comp_cab->user_id = auth()->user()->id;
        $orden_comp_cab->save();

        return response()->json([
            'mensaje' => 'Registro confirmado con éxito',
            'tipo' => 'success',
            'registro' => $orden_comp_cab
        ], 200);
    }

    public function rechazar(Request $request, $id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'rechazar');
        if ($permiso) {
            return $permiso;
        }

        $orden_comp_cab = Orden_comp_cab::find($id);

        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }

        if ($orden_comp_cab->orden_comp_estado != 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden rechazar órdenes de compra en estado CONFIRMADO',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $orden_comp_cab->orden_comp_estado = 'RECHAZADO';
            $orden_comp_cab->user_id = auth()->user()->id;
            $orden_comp_cab->save();

            $presup_comp_cab = Presup_comp_cab::find($orden_comp_cab->presup_comp_id);

            if ($presup_comp_cab) {
                $presup_comp_cab->presup_comp_estado = 'APROBADO';
                $presup_comp_cab->user_id = auth()->user()->id;
                $presup_comp_cab->save();
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Registro rechazado con éxito',
                'tipo' => 'success',
                'registro' => $orden_comp_cab
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al rechazar la orden de compra: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }

    public function aprobar(Request $request, $id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'aprobar');
        if ($permiso) {
            return $permiso;
        }
        
        $orden_comp_cab = Orden_comp_cab::find($id);

        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }

        if ($orden_comp_cab->orden_comp_estado != 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden aprobar órdenes de compra en estado CONFIRMADO',
                'tipo' => 'error'
            ], 400);
        }

        if (
            empty($orden_comp_cab->user_id) ||
            empty($orden_comp_cab->proveedor_id) ||
            empty($orden_comp_cab->sucursal_id) ||
            empty($orden_comp_cab->empresa_id) ||
            empty($orden_comp_cab->presup_comp_id) ||
            empty($orden_comp_cab->pedido_comp_id) ||
            empty($orden_comp_cab->tipo_fact_id) ||
            empty($orden_comp_cab->orden_comp_fec)
        ) {
            return response()->json([
                'mensaje' => 'La cabecera de la orden de compra contiene datos incompletos',
                'tipo' => 'error'
            ], 400);
        }

        $tieneDetalle = DB::table('orden_comp_det')
            ->where('orden_comp_id', $id)
            ->exists();

        if (!$tieneDetalle) {
            return response()->json([
                'mensaje' => 'No se puede aprobar una orden de compra sin detalles',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $orden_comp_cab->orden_comp_estado = 'APROBADO';
            $orden_comp_cab->orden_comp_fec_aprob = now();
            $orden_comp_cab->user_id = auth()->user()->id;
            $orden_comp_cab->save();

            DB::commit();

            return response()->json([
                'mensaje' => 'Registro aprobado con éxito',
                'tipo' => 'success',
                'registro' => $orden_comp_cab
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al aprobar la orden de compra: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }

    public function buscar(Request $r)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("
            SELECT 
                occ.id,
                to_char(occ.orden_comp_fec, 'dd/mm/yyyy HH24:mi:ss') AS orden_comp_fec,
                to_char(occ.orden_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') AS orden_comp_fec_aprob,
                occ.orden_comp_estado,

                occ.empresa_id,  
                e.empresa_desc,

                occ.sucursal_id, 
                s.suc_desc,

                occ.proveedor_id,
                pr.proveedor_desc,

                occ.user_id,
                u.name AS name,

                occ.pedido_comp_id,
                occ.presup_comp_id,
                occ.id AS orden_comp_id,

                occ.tipo_fact_id,
                tf.tipo_fact_desc,

                occ.orden_comp_ifv,

                'ORDEN NRO:' || to_char(occ.id, '0000000') || 
                ' - PROVEEDOR: ' || pr.proveedor_desc ||
                ' - CONDICIÓN: ' || tf.tipo_fact_desc ||
                ' - IFV: ' || COALESCE(occ.orden_comp_ifv::text, '0') ||
                ' - FECHA APROB: ' || to_char(occ.orden_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') ||
                ' (' || occ.orden_comp_estado || ')' AS orden

            FROM orden_comp_cab occ 
            JOIN empresas e ON e.id = occ.empresa_id
            JOIN sucursales s ON s.id = occ.sucursal_id 
            JOIN proveedores pr ON pr.id = occ.proveedor_id
            JOIN users u ON u.id = occ.user_id
            JOIN tipo_fact tf ON tf.id = occ.tipo_fact_id

            WHERE occ.orden_comp_estado = 'APROBADO'
            AND (
                CAST(occ.id AS TEXT) ILIKE ?
                OR pr.proveedor_desc ILIKE ?
                OR CAST(occ.presup_comp_id AS TEXT) ILIKE ?
                OR CAST(occ.pedido_comp_id AS TEXT) ILIKE ?
            )
            ORDER BY occ.id DESC
        ", [
            '%' . $r->name . '%',
            '%' . $r->name . '%',
            '%' . $r->name . '%',
            '%' . $r->name . '%'
        ]);
    }
}
