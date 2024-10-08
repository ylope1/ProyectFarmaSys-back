<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ciudade;
use Illuminate\Support\Facades\DB;

class CiudadeController extends Controller
{
    public function read(){
        return DB::select("select c.*, p.pais_desc from ciudades c join paises p on p.id = c.pais_id");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'ciudad_desc'=>'required',
            'pais_id'=>'required'
        ]);
        $ciudade = Ciudade::create($datosValidados);
        $ciudade->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'ciudades'=>'success',
            'registro'=> $ciudade
        ],200);
    }
    public function update(Request $request, $id){
        $ciudade = Ciudade::find($id);
        if(!$ciudade){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'ciudades'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'ciudad_desc'=>'required',
            'pais_id'=>'required'
        ]);
        $ciudade->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'ciudades'=>'success',
            'registro'=> $ciudade
        ],200);

    }
    public function destroy ($id){
        $ciudade = Ciudade::find($id);
        if(!$ciudade){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'ciudades'=> 'error'
            ],404);
        }
        $ciudade->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'ciudades'=>'success'
        ],200);
    }
    // Función para buscar ciudades
    public function buscar(Request $request){
        $query = $request->input('ciudad_desc'); // Obtener el valor de 'ciudad_desc' del frontend
        $ciudade = Ciudade::where('ciudad_desc', 'LIKE', "%{$query}%")->get(); // Filtrar ciudades por el nombre

        if($ciudade->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }

        return response()->json($ciudade, 200); // Retornar los resultados en formato JSON
    }

}
