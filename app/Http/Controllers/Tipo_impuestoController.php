<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo_impuesto;
use Illuminate\Support\Facades\DB;

class Tipo_impuestoController extends Controller
{
    public function read(){
        return Tipo_impuesto::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'impuesto_desc'=>'required'
        ]);
        $tipo_imp = Tipo_impuesto::create($datosValidados);
        $tipo_imp->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $tipo_imp
        ],200);
    }
    public function update(Request $request, $id){
        $tipo_imp = Tipo_impuesto::find($id);
        if(!$tipo_imp){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'impuesto_desc'=>'required'
        ]);
        $tipo_imp->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $tipo_imp
        ],200);

    }
    public function destroy ($id){
        $tipo_imp = Tipo_impuesto::find($id);
        if(!$tipo_imp){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $tipo_imp->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar tipo impuesto
    public function buscar(Request $request){
        return DB::select("select ti.id as impuesto_id, ti.impuesto_desc 
        from tipo_impuestos ti
        where ti.impuesto_desc ilike '%$request->impuesto_desc%';");
    }
}
