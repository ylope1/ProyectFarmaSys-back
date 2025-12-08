<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden_comp_cab;
use App\Models\Orden_comp_det;
use App\Models\Pedido_comp_cab;
use App\Models\Pedido_comp_det;
use App\Models\Presup_comp_cab;
use App\Models\Presup_comp_det;
use Illuminate\Support\Facades\DB;

class Orden_comp_cabController extends Controller
{
    public function read() { 
        return DB::select("select 
        occ.*,
        to_char(occ.orden_comp_fec, 'dd/mm/yyyy HH24:mi:ss') as orden_comp_fec,
        to_char(occ.orden_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') as orden_comp_fec_aprob,
        p.proveedor_desc,
        e.empresa_desc,
        s.suc_desc,
        u.name,
        tf.tipo_fact_desc,
        case 
            when ped.pedido_comp_estado = 'PROCESADO' then 
                'PEDIDO NRO:' || to_char(occ.pedido_comp_id, '0000000') || 
                ' FECHA PEDIDO: ' || to_char(ped.pedido_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || 
                ' (' || ped.pedido_comp_estado || ')'
            else 'SIN PEDIDO'
        end as pedido,
        case 
            when pre.presup_comp_estado = 'PROCESADO' then 
                'PRESUPUESTO NRO:' || to_char(occ.presup_comp_id, '0000000') || 
                ' FECHA PRESUPUESTO: ' || to_char(pre.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || 
                ' (' || pre.presup_comp_estado || ')'
            else 'SIN PRESUPUESTO'
        end as presupuesto
        from orden_comp_cab occ
        join proveedores p on p.id = occ.proveedor_id
        join empresas e on e.id = occ.empresa_id 
        join sucursales s on s.id = occ.sucursal_id
        join users u on u.id = occ.user_id 
        join tipo_fact tf on tf.id = occ.tipo_fact_id 
        left join pedidos_comp_cab ped on ped.id = occ.pedido_comp_id
        left join presup_comp_cab pre on pre.id = occ.presup_comp_id;");
    }
    public function store(Request $request){
        $datosValidados = $request->validate([
            'presup_comp_id' => 'nullable',
            'pedido_comp_id' => 'nullable',
            'proveedor_id' => 'required',
            'user_id' => 'required',
            'sucursal_id' => 'required',
            'empresa_id' => 'required',
            'tipo_fact_id' => 'required',
            'orden_comp_fec' => 'required',
            'orden_comp_fec_aprob' => 'required',
            'orden_comp_ifv' => 'required',
            'orden_comp_estado' => 'required'
        ]);
        $tienePedido = !empty($request->pedido_comp_id);
        $tienePresupuesto = !empty($request->presup_comp_id);

        if (!$tienePedido && !$tienePresupuesto) {
            return response()->json([
                'mensaje' => 'Debe asociar al menos un pedido CONFIRMADO o un presupuesto APROBADO.',
                'tipo' => 'error'
            ], 422);
        }

        $datosValidados['pedido_comp_id'] = $tienePedido ? $request->pedido_comp_id : null;
        $datosValidados['presup_comp_id'] = $tienePresupuesto ? $request->presup_comp_id : null;

        // Crear la cabecera de orden de compra
        $orden_comp_cab = Orden_comp_cab::create($datosValidados);

        // Si se está generando a partir de un pedido
        if ($tienePedido) {
            $pedido = Pedido_comp_cab::find($request->pedido_comp_id);
            if ($pedido) {
                $pedido->pedido_comp_estado = "PROCESADO";
                $pedido->save();

                $detalles = DB::select("
                    SELECT pd.*, p.prod_precio_comp 
                    FROM pedidos_comp_det pd
                    JOIN productos p ON p.id = pd.producto_id
                    WHERE pedido_comp_id = ?
                ", [$request->pedido_comp_id]);

                foreach ($detalles as $dp) {
                    Orden_comp_det::create([
                        'orden_comp_id' => $orden_comp_cab->id,
                        'producto_id' => $dp->producto_id,
                        'orden_comp_cant' => $dp->pedido_comp_cant,
                        'orden_comp_costo' => $dp->prod_precio_comp
                    ]);
                }
            }
        }

        // Si se está generando a partir de un presupuesto
        elseif ($tienePresupuesto) {
            $presupuesto = Presup_comp_cab::find($request->presup_comp_id);
            if ($presupuesto) {
                $presupuesto->presup_comp_estado = "PROCESADO";
                $presupuesto->save();

                $detalles = DB::select("
                    SELECT pd.*, p.prod_precio_comp 
                    FROM presup_comp_det pd
                    JOIN productos p ON p.id = pd.producto_id
                    WHERE presup_comp_id = ?
                ", [$request->presup_comp_id]);

                foreach ($detalles as $dp) {
                    Orden_comp_det::create([
                        'orden_comp_id' => $orden_comp_cab->id,
                        'producto_id' => $dp->producto_id,
                        'orden_comp_cant' => $dp->presup_comp_cant,
                        'orden_comp_costo' => $dp->prod_precio_comp
                    ]);
                }
            }
        }
        return response()->json([
            'mensaje' => 'Orden de compra creada con éxito',
            'tipo' => 'success',
            'registro' => $orden_comp_cab
        ], 200);
    }
    public function update(Request $request, $id){
        $orden_comp_cab = Orden_comp_cab::find($id);
        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'proveedor_id'=>'required',
            'user_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'tipo_fact_id'=>'required',
            'orden_comp_fec'=>'required',
            'orden_comp_fec_aprob'=> 'required',
            'orden_comp_ifv'=> 'required',
            'orden_comp_estado'=>'required',
        ]);
        
        // Validación personalizada para presup_comp_id y pedido_comp_id
        if (!$request->filled('presup_comp_id') && !$request->filled('pedido_comp_id')) {
            return response()->json([
                'mensaje'=> 'Debe especificar al menos un presupuesto o un pedido',
                'tipo'=> 'error'
            ],422);
        }
        
        if ($request->filled('presup_comp_id') && $request->filled('pedido_comp_id')) {
            return response()->json([
                'mensaje'=> 'No puede especificar ambos: presupuesto y pedido a la vez',
                'tipo'=> 'error'
            ],422);
        }
        
        // Agregamos solo uno de los dos campos válidos al array validado
        if ($request->filled('presup_comp_id')) {
            $datosValidados['presup_comp_id'] = $request->presup_comp_id;
        } elseif ($request->filled('pedido_comp_id')) {
            $datosValidados['pedido_comp_id'] = $request->pedido_comp_id;
        }
        
        $orden_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $orden_comp_cab
        ],200);
    }
    public function destroy ($id){
        $orden_comp_cab = Orden_comp_cab::find($id);
        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $orden_comp_cab->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    public function anular(Request $request, $id){
        $orden_comp_cab = Orden_comp_cab::find($id);
        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $orden_comp_cab->orden_comp_estado = 'ANULADO';
        $orden_comp_cab->user_id = $request->user_id; // registrar quién anuló
        $orden_comp_cab->save();

        // Si fue generado desde un pedido, se vuelve a "CONFIRMADO"
        if ($orden_comp_cab->pedido_comp_id) {
            $pedido = Pedido_comp_cab::find($orden_comp_cab->pedido_comp_id);
            if ($pedido) {
                $pedido->pedido_comp_estado = 'CONFIRMADO';
                $pedido->save();
            }
        }
        // Si fue generado desde un presupuesto, se vuelve a "APROBADO"
        if ($orden_comp_cab->presup_comp_id) {
            $presupuesto = Presup_comp_cab::find($orden_comp_cab->presup_comp_id);
            if ($presupuesto) {
                $presupuesto->presup_comp_estado = 'APROBADO';
                $presupuesto->save();
            }
        }
        return response()->json([
            'mensaje'=> 'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $orden_comp_cab
        ],200);
    }
    public function confirmar(Request $request, $id){
        $orden_comp_cab = Orden_comp_cab::find($id);
        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'presup_comp_id'=>'nullable',
            'proveedor_id'=>'required',
            'user_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'pedido_comp_id'=>'nullable',
            'tipo_fact_id'=>'required',
            'orden_comp_fec'=>'required',
            'orden_comp_fec_aprob'=> 'required',
            'orden_comp_ifv'=> 'required',
            'orden_comp_estado'=>'required'
        ]);
        $orden_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $orden_comp_cab
        ],200);
    }
    public function rechazar(Request $request, $id){
        $orden_comp_cab = Orden_comp_cab::find($id);
        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'presup_comp_id'=>'nullable',
            'proveedor_id'=>'required',
            'user_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'pedido_comp_id'=>'nullable',
            'tipo_fact_id'=>'required',
            'orden_comp_fec'=>'required',
            'orden_comp_fec_aprob'=> 'required',
            'orden_comp_ifv'=> 'required',
            'orden_comp_estado'=>'required'
        ]);
        $orden_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro rechazado con exito',
            'tipo'=>'success',
            'registro'=> $orden_comp_cab
        ],200);
    }
    public function aprobar(Request $request, $id){
        $orden_comp_cab = Orden_comp_cab::find($id);
        if(!$orden_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'presup_comp_id'=>'nullable',
            'proveedor_id'=>'required',
            'user_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'pedido_comp_id'=>'nullable',
            'tipo_fact_id'=>'required',
            'orden_comp_fec'=>'required',
            'orden_comp_fec_aprob'=> 'required',
            'orden_comp_ifv'=> 'required',
            'orden_comp_estado'=>'required'
        ]);
        $orden_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro aprobado con exito',
            'tipo'=>'success',
            'registro'=> $orden_comp_cab
        ],200);
    }

    public function buscar(Request $r){
        return DB::select("SELECT 
            occ.id,
            to_char(occ.orden_comp_fec, 'dd/mm/yyyy HH24:mi:ss') AS orden_comp_fec,
            to_char(occ.orden_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') AS orden_comp_fec_aprob,
            occ.orden_comp_estado,
            occ.empresa_id,  
            e.empresa_desc,
            occ.sucursal_id, 
            s.suc_desc,
            occ.user_id,
            u.name as encargado,
            occ.id as orden_comp_id,
            'ORDEN NRO:' || to_char(occ.id, '0000000') || 
            ' FECHA ORDEN APROB: ' || to_char(occ.orden_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || 
            '(' || occ.orden_comp_estado || ')' AS orden
        FROM orden_comp_cab occ 
        JOIN empresas e ON e.id = occ.empresa_id
        JOIN sucursales s ON s.id = occ.sucursal_id 
        JOIN users u ON u.id = occ.user_id 
        WHERE orden_comp_estado = 'APROBADO' and occ.user_id = {$r->user_id} and u.name ilike '%{$r->name}%';");
    }
}
