<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculos;
use Illuminate\Support\Facades\DB;

class VehiculosController extends Controller
{
    public function read(){
        return Vehiculos::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'vehiculo_desc'=>'required',
            'matricula'=>'required',
            'color'=>'required',
            'tipo'=>'required'
        ]);
        $vehiculo = Vehiculos::create($datosValidados);
        $vehiculo->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $vehiculo
        ],200);
    }
    public function update(Request $request, $id){
        $vehiculo = Vehiculos::find($id);
        if(!$vehiculo){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'vehiculo_desc'=>'required',
            'matricula'=>'required',
            'color'=>'required',
            'tipo'=>'required'
        ]);
        $vehiculo->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $vehiculo
        ],200);

    }
    public function destroy ($id){
        $vehiculo = Vehiculos::find($id);
        if(!$vehiculo){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $vehiculo->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    public function buscar(Request $r){
        return DB::select("select v.id as vehiculo_id, v.* 
        from vehiculos v
        where v.vehiculo_desc ilike '%$r->vehiculo_desc%';");
    }
}
