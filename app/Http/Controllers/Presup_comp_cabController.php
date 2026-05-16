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
        prc.*,
        to_char(prc.presup_comp_fec, 'dd/mm/yyyy HH24:mi:ss' ) as presup_comp_fec,
        to_char(prc.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss' ) as presup_comp_fec_aprob,
        p.proveedor_desc,
        e.empresa_desc,
        s.suc_desc,
        u.name,
        'PEDIDO NRO:' || to_char(prc.pedido_comp_id, '0000000') || ' FECHA PEDIDO: ' || to_char(pcc.pedido_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || '(' || pcc.pedido_comp_estado || ')' AS pedido 
        from presup_comp_cab prc
        join proveedores p on p.id = prc.proveedor_id
        join empresas e on e.id = prc.empresa_id 
        join sucursales s on s.id = prc.sucursal_id
        join users u on u.id = prc.user_id 
        join pedidos_comp_cab pcc on pcc.id = prc.pedido_comp_id;");
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
        pcd.*,
        p.prod_precio_comp 
        from pedidos_comp_det pcd 
        join productos p on p.id = pcd.producto_id 
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
            pcc.user_id, 
            u.name AS name,
            pcc.pedido_comp_id, 
            pcc.id AS presup_comp_id,
            'PRESUPUESTO NRO:' || to_char(pcc.id, '0000000') || 
            ' FECHA PRESUP APROB: ' || to_char(pcc.presup_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || 
            '(' || pcc.presup_comp_estado || ')' AS presupuesto
        FROM presup_comp_cab prc 
        JOIN empresas e ON e.id = prc.empresa_id
        JOIN sucursales s ON s.id = prc.sucursal_id
        JOIN proveedores pr ON pr.id = prc.proveedor_id 
        JOIN users u ON u.id = prc.user_id
        WHERE prc.presup_comp_estado = 'APROBADO' and prc.user_id = {$r->user_id};");
    }
}

