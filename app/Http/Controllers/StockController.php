<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function read(){
        return DB::select("select st.*, su.suc_desc, de.deposito_desc, pr.prod_desc
        from stock st
        join sucursales su on su.id = st.sucursal_id
        join depositos de on de.id = st.deposito_id
        join productos pr on pr.id = st.producto_id;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'stock_cant_exist'=>'required',
            'stock_cant_min'=>'required',
            'stock_cant_max'=>'required',
            'deposito_id'=>'required',
            'sucursal_id'=>'required',
            'producto_id'=>'required',
            'cantidad_exceso'=>'nullable',
            'fecha_movimiento'=>'nullable',
            'motivo'=>'nullable'
        ]);
        $stock = Stock::create($datosValidados);
        
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $stock
        ],200);
    }
    public function update(Request $request, $deposito_id, $sucursal_id, $producto_id){
        $stock = Stock::where('deposito_id', $deposito_id)
                    ->where('sucursal_id', $sucursal_id)
                    ->where('producto_id', $producto_id)
                    ->first();
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
            'cantidad_exceso'=>'nullable',
            'fecha_movimiento'=>'nullable',
            'motivo'=>'nullable'
        ]);
        $stock->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $stock
        ],200);

    }
    public function destroy ($deposito_id, $sucursal_id, $producto_id){
        $stock = Stock::where('deposito_id', $deposito_id)
                    ->where('sucursal_id', $sucursal_id)
                    ->where('producto_id', $producto_id)
                    ->first();
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
