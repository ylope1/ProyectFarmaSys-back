<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function read(){
        return Item::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'item_desc'=>'required'
        ]);
        $item = Item::create($datosValidados);
        $item->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $item
        ],200);
    }
    public function update(Request $request, $id){
        $item = Item::find($id);
        if(!$item){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'item_desc'=>'required'
        ]);
        $item->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $item
        ],200);

    }
    public function destroy ($id){
        $item = Item::find($id);
        if(!$item){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $item->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar items
    public function buscar(Request $request){
        $query = $request->input('item_desc'); // Obtener el valor de 'item_desc' del frontend
        $item = Item::where('item_desc', 'LIKE', "%{$query}%")->get(); // Filtrar items por el nombre

        if($item->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }
        return response()->json($item, 200); // Retornar los resultados en formato JSON
    }
}
