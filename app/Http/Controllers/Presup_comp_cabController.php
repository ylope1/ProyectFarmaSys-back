<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presup_comp_cab;
use App\Models\Pedido_comp_cab;
use App\Models\Presup_comp_det;
use Illuminate\Support\Facades\DB;

class Presup_comp_cabController extends Controller
{
    public function read() {
        return DB::select("select 
        pcc.*,
        to_char(pcc.presup_comp_fec, 'dd/mm/yyyy HH24:mi:ss' ) as presup_comp_fec,
        to_char(pcc.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss' ) as presup_comp_fec_aprob,
        p.proveedor_desc,
        e.empresa_desc,
        s.suc_desc,
        u.name,
        'PEDIDO NRO:' || to_char(pcc.pedido_comp_id, '0000000') || ' FECHA PEDIDO: ' || to_char(pcc2.pedido_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || '(' || pcc2.pedido_comp_estado || ')' AS pedido 
        from presup_comp_cab pcc
        join proveedores p on p.id = pcc.proveedor_id
        join empresas e on e.id = pcc.empresa_id 
        join sucursales s on s.id = pcc.sucursal_id
        join users u on u.id = pcc.user_id 
        join pedidos_comp_cab pcc2 on pcc2.id = pcc.pedido_comp_id;");
    }
    public function store(Request $request){
        $datosValidados = $request->validate([
            'user_id'=>'required',
            'proveedor_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'pedido_comp_id'=>'required',
            'presup_comp_fec'=>'required',
            'presup_comp_fec_aprob'=> 'required',
            'presup_comp_estado'=>'required'
        ]);
        $presup_comp_cab = Presup_comp_cab::create($datosValidados);
        $presup_comp_cab->save();

        $pedido_comp_cab = Pedido_comp_cab::find($request->pedido_comp_id);
        $pedido_comp_cab->pedido_comp_estado = "PROCESADO";
        $pedido_comp_cab->save();

        $pedido_comp_det = DB::select("select 
        pd.*,
        p.prod_precio_comp 
        from pedidos_comp_det pd 
        join productos p on p.id = pd.producto_id 
        where pedido_comp_id = $request->pedido_comp_id;");

        foreach($pedido_comp_det as $dp){
            $presup_comp_det = new Presup_comp_det();
            $presup_comp_det->presup_comp_id = $presup_comp_cab->id;
            $presup_comp_det->producto_id = $dp->producto_id;
            $presup_comp_det->presup_comp_cant = $dp->pedido_comp_cant;
            $presup_comp_det->presup_comp_costo = $dp->prod_precio_comp;
            $presup_comp_det->save();
        }

        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $presup_comp_cab
        ],200);
    }
    public function update(Request $request, $id){
        $presup_comp_cab = Presup_comp_cab::find($id);
        if(!$presup_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }

        $datosValidados = $request->validate([
            'user_id'=>'required',
            'proveedor_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'presup_comp_fec'=>'required',
            'presup_comp_fec_aprob'=> 'required',
            'presup_comp_estado'=>'required'
        ]);
        $presup_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $presup_comp_cab
        ],200);
    }
    public function destroy ($id){
        $presup_comp_cab = Presup_comp_cab::find($id);
        if(!$presup_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $presup_comp_cab->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    public function anular(Request $request, $id){
        $presup_comp_cab = Presup_comp_cab::find($id);
        if(!$presup_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'user_id'=>'required',
            'proveedor_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'presup_comp_fec'=>'required',
            'presup_comp_fec_aprob'=> 'required',
            'presup_comp_estado'=>'required'
        ]);
        $presup_comp_cab->update($datosValidados);

        $pedido_comp_cab = Pedido_comp_cab::find($request->pedido_comp_id);
        $pedido_comp_cab->pedido_comp_estado= "CONFIRMADO";
        $pedido_comp_cab->save();

        return response()->json([
            'mensaje'=> 'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $presup_comp_cab
        ],200);
    }
    public function confirmar(Request $request, $id){
        $presup_comp_cab = Presup_comp_cab::find($id);
        if(!$presup_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'user_id'=>'required',
            'proveedor_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'presup_comp_fec'=>'required',
            'presup_comp_fec_aprob'=> 'required',
            'presup_comp_estado'=>'required'
        ]);
        $presup_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $presup_comp_cab
        ],200);
    }
    public function rechazar(Request $request, $id){
        $presup_comp_cab = Presup_comp_cab::find($id);
        if(!$presup_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'user_id'=>'required',
            'proveedor_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'presup_comp_fec'=>'required',
            'presup_comp_fec_aprob'=> 'required',
            'presup_comp_estado'=>'required'
        ]);
        $presup_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro rechazado con exito',
            'tipo'=>'success',
            'registro'=> $presup_comp_cab
        ],200);
    }
    public function aprobar(Request $request, $id){
        $presup_comp_cab = Presup_comp_cab::find($id);
        if(!$presup_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'user_id'=>'required',
            'proveedor_id'=>'required',
            'sucursal_id'=>'required',
            'empresa_id'=>'required',
            'presup_comp_fec'=>'required',
            'presup_comp_fec_aprob'=> 'required',
            'presup_comp_estado'=>'required'
        ]);
        $presup_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro aprobado con exito',
            'tipo'=>'success',
            'registro'=> $presup_comp_cab
        ],200);
    }
    public function buscar(Request $r){
        return DB::select("SELECT 
            pcc.id,
            to_char(pcc.presup_comp_fec, 'dd/mm/yyyy HH24:mi:ss') AS presup_comp_fec,
            to_char(pcc.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') AS presup_comp_fec_aprob,
            pcc.presup_comp_estado,
            pcc.empresa_id,  
            e.empresa_desc,
            pcc.sucursal_id, 
            s.suc_desc,
            pcc.proveedor_id,
            pr.proveedor_desc,
            pcc.user_id, 
            u.name AS name,
            pcc.pedido_comp_id, 
            pcc.id AS presup_comp_id,
            'PRESUPUESTO NRO:' || to_char(pcc.id, '0000000') || 
            ' FECHA PRESUP APROB: ' || to_char(pcc.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || 
            '(' || pcc.presup_comp_estado || ')' AS presupuesto
        FROM presup_comp_cab pcc 
        JOIN empresas e ON e.id = pcc.empresa_id
        JOIN sucursales s ON s.id = pcc.sucursal_id
        JOIN proveedores pr ON pr.id = pcc.proveedor_id 
        JOIN users u ON u.id = pcc.user_id
        WHERE pcc.presup_comp_estado = 'CONFIRMADO' and pcc.user_id = {$r->user_id};");
    }
}

