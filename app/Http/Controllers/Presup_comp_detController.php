<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presup_comp_det;
use Illuminate\Support\Facades\DB;


class Presup_comp_detController extends Controller
{
    public function read($id){
        return DB::select("select 
		pcd.*, 
		p.prod_desc  
        from presup_comp_det pcd
        join productos p on p.id = pcd.producto_id
        where pcd.presup_comp_id = $id;");
    }
    public function store(Request $request){
        $datosValidados = $request->validate([
            "presup_comp_id"=> "required",
            "producto_id"=> "required",
            "presup_comp_cant"=> "required",
            "presup_comp_costo"=> "required"
        ]);
        $presup_comp_det = Presup_comp_det::create($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $presup_comp_det
        ],200);
    }
    public function update(Request $request, $presup_comp_id, $producto_id){
        $presup_comp_det = DB::table('presup_comp_det')
        ->where('presup_comp_id', $presup_comp_id)
        ->where('producto_id', $producto_id)
        ->update(['presup_comp_costo'=>$request->presup_comp_costo]);

        $presup_comp_det = DB::select("select * from presup_comp_det where presup_comp_id=$presup_comp_id and producto_id=$producto_id");

        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=> 'success',
            'registro'=> $presup_comp_det
        ],200);
    }
    public function destroy($presup_comp_id, $producto_id){
        $presup_comp_det = DB::table('presup_comp_det')
        ->where('presup_comp_id', $presup_comp_id)
        ->where('producto_id', $producto_id)
        ->delete();

        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=> 'success'
        ],200);
    }
}

