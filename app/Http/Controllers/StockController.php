<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function read(){
        return DB::select("select st.*, de.deposito_desc, pr.prod_desc
from stock st
join depositos de on de.id = st.deposito_id
join productos pr on pr.id = st.producto_id;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'stock_cant_exist'=>'required',
            'stock_cant_min'=>'required',
            'stock_cant_max'=>'required',
            'deposito_id'=>'required',
            'producto_id'=>'required'
        ]);
        $stock = Stock::create($datosValidados);
        $stock->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $stock
        ],200);
    }
    public function update(Request $request, $id){
        $stock = Stock::find($id);
        if(!$stock){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'stock_cant_exist'=>'required',
            'stock_cant_min'=>'required',
            'stock_cant_max'=>'required',
            'deposito_id'=>'required',
            'producto_id'=>'required'
        ]);
        $stock->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $stock
        ],200);

    }
    public function destroy ($id){
        $stock = Stock::find($id);
        if(!$stock){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $stock->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
}
