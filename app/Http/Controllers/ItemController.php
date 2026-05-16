<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

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
        return DB::select("select i.id as item_id, i.item_desc 
        from items i
        where i.item_desc ilike '%$request->item_desc%';");
    }
}
