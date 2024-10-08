<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cargo;

class CargoController extends Controller
{
    public function read(){
        return Cargo::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'cargo_desc'=>'required'
        ]);
        $cargo = Cargo::create($datosValidados);
        $cargo->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $cargo
        ],200);
    }
    public function update(Request $request, $id){
        $cargo = Cargo::find($id);
        if(!$cargo){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'cargo_desc'=>'required'
        ]);
        $cargo->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $cargo
        ],200);

    }
    public function destroy ($id){
        $cargo = Cargo::find($id);
        if(!$cargo){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $cargo->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar cargos
    public function buscar(Request $request){
        $query = $request->input('cargo_desc'); // Obtener el valor de 'cargo_desc' del frontend
        $cargo = Cargo::where('cargo_desc', 'LIKE', "%{$query}%")->get(); // Filtrar cargos por el nombre

        if($cargo->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }

        return response()->json($cargo, 200); // Retornar los resultados en formato JSON
    }
}
