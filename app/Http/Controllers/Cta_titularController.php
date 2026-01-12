<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cta_titular;

class Cta_titularController extends Controller
{
    public function read()
    {
        return DB::select("
            select
                ct.cta_bancaria_id,
                cb.cta_banc_banco,
                cb.cta_banc_nro_cuenta,
                ct.titular_id,
                t.tit_nombre,
                t.tit_apellido,
                ct.rol,
                ct.firma_habilitada,
                ct.estado
            from cta_titular ct
            join cta_bancarias cb on cb.id = ct.cta_bancaria_id
            join titulares t on t.id = ct.titular_id
            order by cb.cta_banc_banco, t.tit_nombre
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'cta_bancaria_id' => 'required',
            'titular_id'      => 'required',
            'rol'             => 'required',
            'firma_habilitada'=> 'required',
            'estado'          => 'required'
        ]);

        // Verificar si ya existe la relación
        $existe = Cta_titular::where('cta_bancaria_id', $request->cta_bancaria_id)
            ->where('titular_id', $request->titular_id)
            ->first();

        if ($existe) {
            return response()->json([
                'mensaje' => 'El titular ya está asociado a esta cuenta bancaria',
                'tipo'    => 'error'
            ], 409);
        }

        $registro = Cta_titular::create($datosValidados);
        $registro->save();

        return response()->json([
            'mensaje'  => 'Titular asociado a la cuenta con éxito',
            'tipo'     => 'success',
            'registro' => $registro
        ], 200);
    }

    public function destroy(Request $request)
    {
        $registro = Cta_titular::where('cta_bancaria_id', $request->cta_bancaria_id)
            ->where('titular_id', $request->titular_id)
            ->first();

        if (!$registro) {
            return response()->json([
                'mensaje' => 'Relación no encontrada',
                'tipo'    => 'error'
            ], 404);
        }

        $registro->update([
            'estado' => 'INACTIVO'
        ]);

        return response()->json([
            'mensaje' => 'Relación desactivada con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    public function buscar(Request $r)
    {
        $condiciones = "";

        if ($r->texto) {
            $texto = strtoupper($r->texto);
            $condiciones .= "
                and (
                    cb.cta_banc_banco ILIKE '%$texto%' or
                    cb.cta_banc_nro_cuenta ILIKE '%$texto%' or
                    t.tit_nombre ILIKE '%$texto%' or
                    t.tit_apellido ILIKE '%$texto%'
                )
            ";
        }

        // Solo cuentas activas y titulares activos
        $condiciones .= " and ct.estado = 'ACTIVO'";

        return DB::select("
            select
                ct.cta_bancaria_id,
                ct.titular_id,

                -- texto para el front
                cb.cta_banc_banco || ' - ' || cb.cta_banc_nro_cuenta as cta_banc_desc,
                t.tit_nombre || ' ' || t.tit_apellido as titular_desc,

                ct.rol,
                ct.firma_habilitada,
                ct.estado
            from cta_titular ct
            join cta_bancarias cb on cb.id = ct.cta_bancaria_id
            join titulares t on t.id = ct.titular_id
            where 1=1
            $condiciones
            order by cb.cta_banc_banco, t.tit_nombre
        ");
    }
}
