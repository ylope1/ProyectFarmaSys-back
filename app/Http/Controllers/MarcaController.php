<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marca;

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
    // Función para buscar marcas
    public function buscar(Request $request){
        $query = $request->input('marca_desc'); // Obtener el valor de 'marca_desc' del frontend
        $marca = Marca::where('marca_desc', 'LIKE', "%{$query}%")->get(); // Filtrar marcas por el nombre

        if($marca->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }

        return response()->json($marca, 200); // Retornar los resultados en formato JSON
    }
}
