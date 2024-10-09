<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rubros;
use Illuminate\Support\Facades\DB;


class RubroController extends Controller
{
    public function read(){
        return Rubros::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'rubro_desc'=>'required'
        ]);
        $rubro = Rubros::create($datosValidados);
        $rubro->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $rubro
        ],200);
    }
    public function update(Request $request, $id){
        $rubro = Rubros::find($id);
        if(!$rubro){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'rubro_desc'=>'required'
        ]);
        $rubro->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $rubro
        ],200);

    }
    public function destroy ($id){
        $rubro = Rubros::find($id);
        if(!$rubro){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $rubro->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar rubros
     public function buscar(Request $request){
        return DB::select("select r.id as rubro_id, r.*
        from rubros r  
        where r.rubro_desc ilike '%$request->rubro_desc%';");
    }
}
