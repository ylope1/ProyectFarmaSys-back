<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

class EmpresaController extends Controller
{
    public function read(){
        return Empresa::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'empresa_desc'=>'required',
            'empresa_ruc'=>'required',
            'empresa_direc'=>'required',
            'empresa_telef'=>'required',
            'empresa_email'=>'required'
        ]);
        $empresa = Empresa::create($datosValidados);
        $empresa->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $empresa
        ],200);
    }
    public function update(Request $request, $id){
        $empresa = Empresa::find($id);
        if(!$empresa){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'empresa_desc'=>'required',
            'empresa_ruc'=>'required',
            'empresa_direc'=>'required',
            'empresa_telef'=>'required',
            'empresa_email'=>'required'
        ]);
        $empresa->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $empresa
        ],200);

    }
    public function destroy ($id){
        $empresa = Empresa::find($id);
        if(!$empresa){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $empresa->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }

    // Función para buscar empresas
    public function buscar(Request $r){
        return DB::select("select e.id as empresa_id, e.* 
        from empresas e
        where e.empresa_desc ilike '%$r->empresa_desc%';");
    }
}
