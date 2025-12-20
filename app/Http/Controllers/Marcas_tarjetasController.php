<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Marcas_tarjetas;

class Marcas_tarjetasController extends Controller
{
    public function read(){
        return DB::select("select * from marcas_tarjetas;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'marca_desc' => 'required',
            'marca_estado' => 'required'
        ]);

        $marcas_tarjetas = Marcas_tarjetas::create($datosValidados);
        $marcas_tarjetas->save();

        return response()->json([
            'mensaje' => 'Registro creado con exito',
            'tipo' => 'success',
            'registro' => $marcas_tarjetas
        ], 200);
    }

    public function update(Request $request, $id){
        $marcas_tarjetas = Marcas_tarjetas::find($id);

        if(!$marcas_tarjetas){
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'marca_desc' => 'required',
            'marca_estado' => 'required'
        ]);

        $marcas_tarjetas->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con exito',
            'tipo' => 'success',
            'registro' => $marcas_tarjetas
        ], 200);
    }

    public function destroy($id){
        $marcas_tarjetas = Marcas_tarjetas::find($id);

        if(!$marcas_tarjetas){
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $marcas_tarjetas->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con exito',
            'tipo' => 'success'
        ], 200);
    }

    public function buscar(Request $r){
        return DB::select("
            select *
            from marcas_tarjetas
            where marca_desc ilike '%$r->marca_desc%';
        ");
    }
}
