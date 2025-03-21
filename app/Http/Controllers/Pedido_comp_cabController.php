<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido_comp_cab;
use Illuminate\Support\Facades\DB;

class Pedido_comp_cabController extends Controller
{
    public function read() {
        return DB::select("SELECT 
            pcc.id,
            to_char(pcc.pedido_comp_fec, 'dd/mm/yyyy HH24:mi:ss') AS pedido_comp_fec,
            to_char(pcc.pedido_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') AS pedido_comp_fec_aprob,
            pcc.pedido_comp_estado,
            pcc.empresa_id,  
            e.empresa_desc,
            pcc.sucursal_id, 
            s.suc_desc,
            pcc.funcionario_id, 
            u.name AS func_nombre
        FROM pedidos_comp_cab pcc 
        JOIN empresas e ON e.id = pcc.empresa_id
        JOIN sucursales s ON s.id = pcc.sucursal_id 
        JOIN funcionarios f ON f.id = pcc.funcionario_id
        JOIN users u ON f.user_id = u.id;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'pedido_comp_fec'=>'required',
            'pedido_comp_fec_aprob'=>'required',
            'pedido_comp_estado'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'funcionario_id'=>'required'
        ]);

        $pedido_comp_cab = Pedido_comp_cab::create($datosValidados);
        $pedido_comp_cab->save();

        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $pedido_comp_cab
        ],200);
    }

    public function update(Request $request, $id){
        $pedido_comp_cab = Pedido_comp_cab::find($id);
        if(!$pedido_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }

        $datosValidados = $request->validate([
            'pedido_comp_fec'=>'required',
            'pedido_comp_fec_aprob'=>'required',
            'pedido_comp_estado'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'funcionario_id'=>'required'
        ]);

        $pedido_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $pedido_comp_cab
        ],200);

    }
    public function destroy ($id){
        $pedido_comp_cab = Pedido_comp_cab::find($id);
        if(!$pedido_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $pedido_comp_cab->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    public function anular(Request $request, $id){
        $pedido_comp_cab = Pedido_comp_cab::find($id);
        if(!$pedido_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'pedido_comp_fec'=>'required',
            'pedido_comp_fec_aprob'=>'required',
            'pedido_comp_estado'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'funcionario_id'=>'required'
        ]);

        $pedido_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $pedido_comp_cab
        ],200);
    }
    public function confirmar(Request $request, $id){
        $pedido_comp_cab = Pedido_comp_cab::find($id);
        if(!$pedido_comp_cab){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'pedido_comp_fec'=>'required',
            'pedido_comp_fec_aprob'=>'required',
            'pedido_comp_estado'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'funcionario_id'=>'required'
        ]);
        $pedido_comp_cab->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $pedido_comp_cab
        ],200);
    }

    public function buscar(Request $r){
        return DB::select("SELECT 
            pcc.id,
            to_char(pcc.pedido_comp_fec, 'dd/mm/yyyy HH24:mi:ss') AS pedido_comp_fec,
            to_char(pcc.pedido_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') AS pedido_comp_fec_aprob,
            pcc.pedido_comp_estado,
            pcc.empresa_id,  
            e.empresa_desc,
            pcc.sucursal_id, 
            s.suc_desc,
            pcc.funcionario_id, 
            u.name AS func_nombre,
            pcc.id as pedido_comp_id,
            'PEDIDO NRO:' || to_char(pcc.id, '0000000') || 
            ' FECHA PEDIDO APROB: ' || to_char(pcc.pedido_comp_fec_aprob, 'dd/mm/yyyy HH24:mi:ss') || 
            '(' || pcc.pedido_comp_estado || ')' AS pedido
        FROM pedidos_comp_cab pcc 
        JOIN empresas e ON e.id = pcc.empresa_id
        JOIN sucursales s ON s.id = pcc.sucursal_id 
        JOIN funcionarios f ON f.id = pcc.funcionario_id
        JOIN users u ON f.user_id = u.id 
        WHERE pedido_comp_estado = 'CONFIRMADO' and pcc.funcionario_id = {$r->user_id} and u.name ilike '%{$r->name}%';");
    }
}