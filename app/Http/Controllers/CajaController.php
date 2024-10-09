<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cajas;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function read(){
        return Cajas::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'caja_desc'=>'required'
        ]);
        $caja = Cajas::create($datosValidados);
        $caja->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $caja
        ],200);
    }
    public function update(Request $request, $id){
        $caja = Cajas::find($id);
        if(!$caja){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'caja_desc'=>'required'
        ]);
        $caja->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $caja
        ],200);

    }
    public function destroy ($id){
        $caja = Cajas::find($id);
        if(!$caja){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $caja->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar cajas
     public function buscar(Request $request){
        return DB::select("select c.id as caja_id, c.*
        from cajas c 
        where c.caja_desc ilike '%$request->caja_desc%';");
    }
}
