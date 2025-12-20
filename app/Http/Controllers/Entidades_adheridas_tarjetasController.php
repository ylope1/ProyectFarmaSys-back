<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entidades_adheridas_tarjetas;
use Illuminate\Support\Facades\DB;

class Entidades_adheridas_tarjetasController extends Controller
{
    public function read()
    {
        return DB::select("
            select 
                eat.id,
                ea.ent_adhe_desc,
                ee.ent_emi_desc,
                mt.marca_desc,
                eat.estado
            from entidades_adheridas_tarjetas eat
            join entidades_adheridas ea on ea.id = eat.entidad_adherida_id
            join entidades_emisoras ee on ee.id = eat.entidad_emisora_id
            join marcas_tarjetas mt on mt.id = eat.marca_tarjeta_id
            order by ea.ent_adhe_desc, ee.ent_emi_desc, mt.marca_desc
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'entidad_adherida_id' => 'required',
            'entidad_emisora_id'  => 'required',
            'marca_tarjeta_id'    => 'required',
            'estado'              => 'required',
        ]);

        $registro = Entidades_adheridas_tarjetas::create($datosValidados);
        $registro->save();

        return response()->json([
            'mensaje' => 'Combinación registrada con éxito',
            'tipo'    => 'success',
            'registro'=> $registro
        ], 200);
    }

    public function destroy($id)
    {
        $registro = Entidades_adheridas_tarjetas::find($id);

        if (!$registro) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $registro->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    public function buscar(Request $r)
    {
        $condiciones = "";

        if ($r->entidad_adherida_id) {
            $condiciones .= " and eat.entidad_adherida_id = $r->entidad_adherida_id";
        }

        if ($r->entidad_emisora_id) {
            $condiciones .= " and eat.entidad_emisora_id = $r->entidad_emisora_id";
        }

        if ($r->marca_tarjeta_id) {
            $condiciones .= " and eat.marca_tarjeta_id = $r->marca_tarjeta_id";
        }

        return DB::select("
            select 
                eat.id,
                ea.ent_adhe_desc,
                ee.ent_emi_desc,
                mt.marca_desc,
                eat.estado
            from entidades_adheridas_tarjetas eat
            join entidades_adheridas ea on ea.id = eat.entidad_adherida_id
            join entidades_emisoras ee on ee.id = eat.entidad_emisora_id
            join marcas_tarjetas mt on mt.id = eat.marca_tarjeta_id
            where 1=1
            $condiciones
            order by ea.ent_adhe_desc, ee.ent_emi_desc, mt.marca_desc
        ");
    }
}
