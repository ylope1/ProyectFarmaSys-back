<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presup_comp_cab;
use App\Models\Pedido_comp_cab;
use App\Models\Presup_comp_det;
use App\Traits\VerificaPermisos;
use Illuminate\Support\Facades\DB;

class Presup_comp_cabController extends Controller
{
    use VerificaPermisos;

    private $rutaPermiso = 'movimientos/compras/presupuestos/';

    public function read() {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }
        return DB::select("select 
            prc.*,
            to_char(prc.presup_comp_fec, 'dd/mm/yyyy HH24:mi:ss' ) as presup_comp_fec,
            to_char(prc.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss' ) as presup_comp_fec_aprob,
            p.proveedor_desc,
            e.empresa_desc,
            s.suc_desc,
            u.name,
            'PEDIDO NRO:' || to_char(prc.pedido_comp_id, '0000000') || 
            ' FECHA PEDIDO: ' || to_char(pcc.pedido_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || '(' || pcc.pedido_comp_estado || ')' AS pedido 
            from presup_comp_cab prc
            join proveedores p on p.id = prc.proveedor_id
            join empresas e on e.id = prc.empresa_id 
            join sucursales s on s.id = prc.sucursal_id
            join users u on u.id = prc.user_id 
            join pedidos_comp_cab pcc on pcc.id = prc.pedido_comp_id
            order by prc.id desc
        ;");
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
            'proveedor_id' => 'required|exists:proveedores,id',
            'pedido_comp_id' => 'required|exists:pedidos_comp_cab,id',
            'presup_comp_fec' => 'required'
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

        $pedido_comp_cab = Pedido_comp_cab::find($request->pedido_comp_id);

        if (!$pedido_comp_cab) {
            return response()->json([
                'mensaje' => 'Pedido de compra no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($pedido_comp_cab->pedido_comp_estado != 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'Solo se puede generar presupuesto desde un pedido confirmado',
                'tipo' => 'error'
            ], 400);
        }

        $tieneDetalle = DB::table('pedidos_comp_det')
            ->where('pedido_comp_id', $request->pedido_comp_id)
            ->exists();

        if (!$tieneDetalle) {
            return response()->json([
                'mensaje' => 'No se puede generar presupuesto desde un pedido sin detalles',
                'tipo' => 'error'
            ], 400);
        }

        $existePresupuesto = Presup_comp_cab::where('pedido_comp_id', $request->pedido_comp_id)
            ->where('proveedor_id', $request->proveedor_id)
            ->whereIn('presup_comp_estado', ['PENDIENTE', 'CONFIRMADO', 'APROBADO'])
            ->first();

        if ($existePresupuesto) {
            return response()->json([
                'mensaje' => 'Ya existe un presupuesto activo para este pedido y proveedor',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $presup_comp_cab = Presup_comp_cab::create([
                'user_id' => $user->id,
                'proveedor_id' => $request->proveedor_id,
                'sucursal_id' => $funcionario->sucursal_id,
                'empresa_id' => $funcionario->empresa_id,
                'pedido_comp_id' => $request->pedido_comp_id,
                'presup_comp_fec' => $request->presup_comp_fec,
                'presup_comp_fec_aprob' => null,
                'presup_comp_estado' => 'PENDIENTE'
            ]);

            $pedido_comp_cab->pedido_comp_estado = "PROCESADO";
            $pedido_comp_cab->save();

            $pedido_comp_det = DB::select("
                select 
                    pcd.*,
                    p.prod_precio_comp 
                from pedidos_comp_det pcd 
                join productos p on p.id = pcd.producto_id 
                where pcd.pedido_comp_id = ?
            ", [$request->pedido_comp_id]);

            foreach ($pedido_comp_det as $dp) {
                $presup_comp_det = new Presup_comp_det();
                $presup_comp_det->presup_comp_id = $presup_comp_cab->id;
                $presup_comp_det->producto_id = $dp->producto_id;
                $presup_comp_det->presup_comp_cant = $dp->pedido_comp_cant;
                $presup_comp_det->presup_comp_costo = $dp->prod_precio_comp;
                $presup_comp_det->save();
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Registro creado con éxito',
                'tipo' => 'success',
                'registro' => $presup_comp_cab
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al registrar el presupuesto: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $presup_comp_cab = Presup_comp_cab::find($id);

        if (!$presup_comp_cab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($presup_comp_cab->presup_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede modificar un presupuesto pendiente',
                'tipo' => 'error'
            ], 400);
        }

        $datosValidados = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'presup_comp_fec' => 'required|date'
        ]);

        $existePresupuesto = Presup_comp_cab::where('pedido_comp_id', $presup_comp_cab->pedido_comp_id)
            ->where('proveedor_id', $request->proveedor_id)
            ->where('id', '<>', $id)
            ->whereIn('presup_comp_estado', ['PENDIENTE', 'CONFIRMADO', 'APROBADO'])
            ->first();

        if ($existePresupuesto) {
            return response()->json([
                'mensaje' => 'Ya existe otro presupuesto activo para este pedido y proveedor',
                'tipo' => 'error'
            ], 400);
        }

        $datosValidados['user_id'] = auth()->user()->id;

        $presup_comp_cab->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $presup_comp_cab
        ], 200);
    }

    public function anular($id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular');
        if ($permiso) {
            return $permiso;
        }

        $presup_comp_cab = Presup_comp_cab::find($id);

        if (!$presup_comp_cab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($presup_comp_cab->presup_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden anular presupuestos en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $presup_comp_cab->presup_comp_estado = 'ANULADO';
            $presup_comp_cab->user_id = auth()->user()->id;
            $presup_comp_cab->save();

            $pedido_comp_cab = Pedido_comp_cab::find($presup_comp_cab->pedido_comp_id);

            if ($pedido_comp_cab) {
                $pedido_comp_cab->pedido_comp_estado = 'CONFIRMADO';
                $pedido_comp_cab->save();
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Registro anulado con éxito',
                'tipo' => 'success',
                'registro' => $presup_comp_cab
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al anular el presupuesto: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }

    public function confirmar($id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'confirmar');
        if ($permiso) {
            return $permiso;
        }

        $presup_comp_cab = Presup_comp_cab::find($id);

        if (!$presup_comp_cab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($presup_comp_cab->presup_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar presupuestos en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        if (
            empty($presup_comp_cab->user_id) ||
            empty($presup_comp_cab->proveedor_id) ||
            empty($presup_comp_cab->sucursal_id) ||
            empty($presup_comp_cab->empresa_id) ||
            empty($presup_comp_cab->pedido_comp_id) ||
            empty($presup_comp_cab->presup_comp_fec)
        ) {
            return response()->json([
                'mensaje' => 'La cabecera del presupuesto contiene datos incompletos',
                'tipo' => 'error'
            ], 400);
        }

        $tieneDetalle = DB::table('presup_comp_det')
            ->where('presup_comp_id', $id)
            ->exists();

        if (!$tieneDetalle) {
            return response()->json([
                'mensaje' => 'No se puede confirmar un presupuesto sin detalles',
                'tipo' => 'error'
            ], 400);
        }

        $presup_comp_cab->presup_comp_estado = 'CONFIRMADO';
        $presup_comp_cab->user_id = auth()->user()->id;
        $presup_comp_cab->save();

        return response()->json([
            'mensaje' => 'Registro confirmado con éxito',
            'tipo' => 'success',
            'registro' => $presup_comp_cab
        ], 200);
    }

    public function rechazar($id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'rechazar');
        if ($permiso) {
            return $permiso;
        }

        $presup_comp_cab = Presup_comp_cab::find($id);

        if (!$presup_comp_cab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($presup_comp_cab->presup_comp_estado != 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden rechazar presupuestos en estado CONFIRMADO',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $presup_comp_cab->presup_comp_estado = 'RECHAZADO';
            $presup_comp_cab->user_id = auth()->user()->id;
            $presup_comp_cab->save();

            $pedido_comp_cab = Pedido_comp_cab::find($presup_comp_cab->pedido_comp_id);

            if ($pedido_comp_cab) {
                $pedido_comp_cab->pedido_comp_estado = 'CONFIRMADO';
                $pedido_comp_cab->save();
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Registro rechazado con éxito',
                'tipo' => 'success',
                'registro' => $presup_comp_cab
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al rechazar el presupuesto: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }
    public function aprobar($id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'aprobar');
        if ($permiso) {
            return $permiso;
        }

        $presup_comp_cab = Presup_comp_cab::find($id);

        if (!$presup_comp_cab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($presup_comp_cab->presup_comp_estado != 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden aprobar presupuestos en estado CONFIRMADO',
                'tipo' => 'error'
            ], 400);
        }

        if (
            empty($presup_comp_cab->user_id) ||
            empty($presup_comp_cab->proveedor_id) ||
            empty($presup_comp_cab->sucursal_id) ||
            empty($presup_comp_cab->empresa_id) ||
            empty($presup_comp_cab->pedido_comp_id) ||
            empty($presup_comp_cab->presup_comp_fec)
        ) {
            return response()->json([
                'mensaje' => 'La cabecera del presupuesto contiene datos incompletos',
                'tipo' => 'error'
            ], 400);
        }

        $tieneDetalle = DB::table('presup_comp_det')
            ->where('presup_comp_id', $id)
            ->exists();

        if (!$tieneDetalle) {
            return response()->json([
                'mensaje' => 'No se puede aprobar un presupuesto sin detalles',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {

            $presup_comp_cab->presup_comp_estado = 'APROBADO';
            $presup_comp_cab->presup_comp_fec_aprob;
            $presup_comp_cab->user_id = auth()->user()->id;
            $presup_comp_cab->save();

            DB::commit();

            return response()->json([
                'mensaje' => 'Registro aprobado con éxito',
                'tipo' => 'success',
                'registro' => $presup_comp_cab
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al aprobar el presupuesto: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }
    public function buscar(Request $r)
    {
        return DB::select("
            SELECT 
                prc.id,
                to_char(prc.presup_comp_fec, 'dd/mm/yyyy HH24:mi:ss') AS presup_comp_fec,
                to_char(prc.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') AS presup_comp_fec_aprob,
                prc.presup_comp_estado,
                prc.empresa_id,  
                e.empresa_desc,
                prc.sucursal_id, 
                s.suc_desc,
                prc.proveedor_id,
                pr.proveedor_desc,
                prc.user_id, 
                u.name AS name,
                prc.pedido_comp_id, 
                prc.id AS presup_comp_id,
                'PRESUPUESTO NRO:' || to_char(prc.id, '0000000') || 
                ' - PROVEEDOR: ' || pr.proveedor_desc ||
                ' - FECHA APROB: ' || to_char(prc.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') ||
                ' (' || prc.presup_comp_estado || ')' AS presupuesto
            FROM presup_comp_cab prc 
            JOIN empresas e ON e.id = prc.empresa_id
            JOIN sucursales s ON s.id = prc.sucursal_id
            JOIN proveedores pr ON pr.id = prc.proveedor_id 
            JOIN users u ON u.id = prc.user_id
            WHERE prc.presup_comp_estado = 'APROBADO'
            AND (
                CAST(prc.id AS TEXT) ILIKE ?
                OR pr.proveedor_desc ILIKE ?
                OR CAST(prc.pedido_comp_id AS TEXT) ILIKE ?
            )
            ORDER BY prc.id DESC
        ", [
            '%' . $r->name . '%',
            '%' . $r->name . '%',
            '%' . $r->name . '%'
        ]);
    }
}

