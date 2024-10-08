<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido_comp_det;
use Illuminate\Support\Facades\DB;

class Pedido_comp_detController extends Controller
{
    public function read($id){
        return DB::select("select pcd.*, p.prod_desc  
        from pedidos_comp_det pcd
        join productos p on p.id = pcd.producto_id
        where pcd.pedido_comp_id = $id;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            "pedido_comp_id"=> "required",
            "producto_id"=> "required",
            "pedido_comp_cant"=> "required",
            "pedido_comp_precio"=> "required",
            "stock_id"=> "required",
            "deposito_id"=> "required"
        ]);
        $pedido_comp_det = Pedido_comp_det::create($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $pedido_comp_det
        ],200);
    }

    public function update(Request $request, $pedido_comp_id, $producto_id){
        $pedido_comp_det = DB::table('pedidos_comp_det')
        ->where('pedido_comp_id', $pedido_comp_id)
        ->where('producto_id', $producto_id)
        ->update(['pedido_comp_cant'=>$request->pedido_comp_cant]);

        $pedido_comp_det = DB::select("select * from pedidos_comp_det where pedido_comp_id=$pedido_comp_id and producto_id=$producto_id");

        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=> 'success',
            'registro'=> $pedido_comp_det
        ],200);
    }
    public function destroy($pedido_comp_id, $producto_id){
        $pedido_comp_det = DB::table('pedidos_comp_det')
        ->where('pedido_comp_id', $pedido_comp_id)
        ->where('producto_id', $producto_id)
        ->delete();

        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=> 'success',
            'registro'=> $pedido_comp_det
        ],200);
    }
}