<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Entidades_adheridas;

class Entidades_adheridasController extends Controller
{
    public function read(){
        return DB::select("select * from entidades_adheridas;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'ent_adhe_desc' => 'required',
            'ent_adhe_direc' => 'nullable',
            'ent_adhe_telef' => 'nullable',
            'ent_adhe_email' => 'nullable',
            'ent_adhe_estado' => 'required'
        ]);

        $entidades_adheridas = Entidades_adheridas::create($datosValidados);
        $entidades_adheridas->save();

        return response()->json([
            'mensaje' => 'Registro creado con exito',
            'tipo' => 'success',
            'registro' => $entidades_adheridas
        ], 200);
    }

    public function update(Request $request, $id){
        $entidades_adheridas = Entidades_adheridas::find($id);

        if(!$entidades_adheridas){
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'ent_adhe_desc' => 'required',
            'ent_adhe_direc' => 'nullable',
            'ent_adhe_telef' => 'nullable',
            'ent_adhe_email' => 'nullable',
            'ent_adhe_estado' => 'required'
        ]);

        $entidades_adheridas->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con exito',
            'tipo' => 'success',
            'registro' => $entidades_adheridas
        ], 200);
    }

    public function destroy($id){
        $entidades_adheridas = Entidades_adheridas::find($id);

        if(!$entidades_adheridas){
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $entidades_adheridas->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con exito',
            'tipo' => 'success'
        ], 200);
    }

    public function buscar(Request $r){
        return DB::select("
            select *
            from entidades_adheridas
            where ent_adhe_desc ilike '%$r->ent_adhe_desc%';
        ");
    }
}
