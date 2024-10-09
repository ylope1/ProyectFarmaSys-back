<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Forma_cobros;
use Illuminate\Support\Facades\DB;

class Forma_cobroController extends Controller
{
    public function read(){
        return Forma_cobros::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'forma_cob_desc'=>'required'
        ]);
        $forma_cobro = Forma_cobros::create($datosValidados);
        $forma_cobro->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $forma_cobro
        ],200);
    }
    public function update(Request $request, $id){
        $forma_cobro = Forma_cobros::find($id);
        if(!$forma_cobro){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'forma_cob_desc'=>'required'
        ]);
        $forma_cobro->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $forma_cobro
        ],200);

    }
    public function destroy ($id){
        $forma_cobro = Forma_cobros::find($id);
        if(!$forma_cobro){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $forma_cobro->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar forma de cobros
     public function buscar(Request $request){
        return DB::select("select fc.id as form_cob_id, fc.*
        from forma_cobros fc 
        where fc.forma_cob_desc ilike '%$request->forma_cob_desc%';");
    }
}
