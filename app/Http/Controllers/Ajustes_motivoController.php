<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ajustes_motivo;

class Ajustes_motivoController extends Controller
{
    public function read(){
        return Ajustes_motivo::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'ajus_mot_desc'=>'required'
        ]);
        $ajuste_motivo = Ajustes_motivo::create($datosValidados);
        $ajuste_motivo->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $ajuste_motivo
        ],200);
    }
    public function update(Request $request, $id){
        $ajuste_motivo = Ajustes_motivo::find($id);
        if(!$ajuste_motivo){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'ajus_mot_desc'=>'required'
        ]);
        $ajuste_motivo->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $ajuste_motivo
        ],200);

    }
    public function destroy ($id){
        $ajuste_motivo = Ajustes_motivo::find($id);
        if(!$ajuste_motivo){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $ajuste_motivo->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar motivos de ajustes
    public function buscar(Request $request){
        $query = $request->input('ajus_mot_desc'); // Obtener el valor de 'ajus_mot_desc' del frontend
        $ajuste_motivo = Ajustes_motivo::where('ajus_mot_desc', 'LIKE', "%{$query}%")->get(); // Filtrar motivo de ajustes por la descripcion

        if($ajuste_motivo->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }

        return response()->json($ajuste_motivo, 200); // Retornar los resultados en formato JSON
    }
}
