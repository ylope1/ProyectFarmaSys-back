<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Documentos;
use Illuminate\Support\Facades\DB;

class DocumentoController extends Controller
{
    public function read(){
        return Documentos::all();
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'documento_desc'=>'required'
        ]);
        $documento = Documentos::create($datosValidados);
        $documento->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $documento
        ],200);
    }
    public function update(Request $request, $id){
        $documento = Documentos::find($id);
        if(!$documento){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'documento_desc'=>'required'
        ]);
        $documento->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $documento
        ],200);

    }
    public function destroy ($id){
        $documento = Documentos::find($id);
        if(!$documento){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $documento->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar documentos
     public function buscar(Request $request){
        return DB::select("select d.id as doc_id, d.*
        from documentos d  
        where d.documento_desc ilike '%$request->documento_desc%';");
    }
}
