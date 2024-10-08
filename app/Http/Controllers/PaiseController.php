<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paises;

class PaiseController extends Controller
{
    public function read(){
        return Paises::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'pais_desc'=>'required', 'string', 'min:3', 'max:20', 'regex:/^[a-zA-Z\s]+$/', 'unique:paises,pais_desc'
        ]);
        $paise = Paises::create($datosValidados);
        $paise->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'paises'=>'success',
            'registro'=> $paise
        ],200);
    }
    public function update(Request $request, $id){
        $paise = Paises::find($id);
        if(!$paise){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'paise'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'pais_desc'=>'required', 'string', 'min:3', 'max:20', 'regex:/^[a-zA-Z\s]+$/', "unique:paises,pais_desc,{$id}"
        ]);
        $paise->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'paises'=>'success',
            'registro'=> $paise
        ],200);

    }
    public function destroy ($id){
        $paise = Paises::find($id);
        if(!$paise){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'paise'=> 'error'
            ],404);
        }
        $paise->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'paises'=>'success'
        ],200);
    }

    // Función para buscar países
    public function buscar(Request $request){
        $query = $request->input('pais_desc'); // Obtener el valor de 'pais_desc' del frontend
        $paise = Paises::where('pais_desc', 'LIKE', "%{$query}%")->get(); // Filtrar países por el nombre

        if($paise->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }
        return response()->json($paise, 200); // Retornar los resultados en formato JSON
    }
}
