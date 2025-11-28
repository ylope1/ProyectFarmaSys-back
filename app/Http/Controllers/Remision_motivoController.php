<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Remision_motivo;
use Illuminate\Support\Facades\DB;

class Remision_motivoController extends Controller
{
    public function read(){
        return Remision_motivo::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'remision_motivo_desc'=>'required'
        ]);
        $remision_motivo = Remision_motivo::create($datosValidados);
        $remision_motivo->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $remision_motivo
        ],200);
    }
    public function update(Request $request, $id){
        $remision_motivo = Remision_motivo::find($id);
        if(!$remision_motivo){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'remision_motivo_desc'=>'required'
        ]);
        $remision_motivo->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $remision_motivo
        ],200);

    }
    public function destroy ($id){
        $remision_motivo = Remision_motivo::find($id);
        if(!$remision_motivo){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $remision_motivo->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    public function buscar(Request $r){
        return DB::select("select rm.id as remision_motivo_id, rm.* 
        from remision_motivo rm
        where rm.remision_motivo_desc ilike '%$r->remision_motivo_desc%';");
    }
}
