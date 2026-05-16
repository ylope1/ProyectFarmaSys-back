<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marca;
use Illuminate\Support\Facades\DB;

class MarcaController extends Controller
{
    public function read(){
        return Marca::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'marca_desc'=>'required'
        ]);
        $marca = Marca::create($datosValidados);
        $marca->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $marca
        ],200);
    }
    public function update(Request $request, $id){
        $marca = Marca::find($id);
        if(!$marca){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'marca_desc'=>'required'
        ]);
        $marca->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $marca
        ],200);

    }
    public function destroy ($id){
        $marca = Marca::find($id);
        if(!$marca){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $marca->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar productos
     public function buscar(Request $request){
        return DB::select("select m.id as marca_id, m.* 
        from marcas m
        where m.marca_desc ilike '%$request->marca_desc%';");
    }
}
