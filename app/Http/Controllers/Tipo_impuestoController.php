<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo_impuesto;

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
        $query = $request->input('impuesto_desc'); // Obtener el valor de 'impuesto_desc' del frontend
        $tipo_imp = Tipo_impuesto::where('impuesto_desc', 'LIKE', "%{$query}%")->get(); // Filtrar tipo de impuestos por la descripcion

        if($tipo_imp->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }
        return response()->json($tipo_imp, 200); // Retornar los resultados en formato JSON
    }
}
