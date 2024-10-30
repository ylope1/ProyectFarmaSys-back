<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presup_comp_cab;
use App\Models\Pedido_comp_cab;
use App\Models\Presup_comp_det;
use Illuminate\Support\Facades\DB;

class Presup_comp_cabController extends Controller
{
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
        $pedido_comp_cab->pedido_comp_estado= "PROCESADO";
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
            $presup_comp_det->presup_comp_costo = $dp->prod_precio_comp;
            $presup_comp_det->presup_comp_cant = $dp->presup_comp_cant;
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
            'pedido_comp_id'=>'required',
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
            'pedido_comp_id'=>'required',
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
            'pedido_comp_id'=>'required',
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
}
