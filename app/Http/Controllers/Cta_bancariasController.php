<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cta_bancarias;

class Cta_bancariasController extends Controller
{
    public function read()
    {
        return DB::select("
            select *
            from cta_bancarias
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'cta_banc_nro_cuenta' => 'required',
            'cta_banc_banco'      => 'required',
            'cta_banc_tipo'       => 'required',
            'cta_banc_moneda'     => 'required',
            'cta_banc_estado'     => 'required'
        ]);

        $cta_bancaria = Cta_bancarias::create($datosValidados);
        $cta_bancaria->save();

        return response()->json([
            'mensaje'  => 'Cuenta bancaria registrada con éxito',
            'tipo'     => 'success',
            'registro' => $cta_bancaria
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $cta_bancaria = Cta_bancarias::find($id);

        if (!$cta_bancaria) {
            return response()->json([
                'mensaje' => 'Cuenta bancaria no encontrada',
                'tipo'    => 'error',
                'registro' => $cta_bancaria
            ], 404);
        }

        $datosValidados = $request->validate([
            'cta_banc_nro_cuenta' => 'required',
            'cta_banc_banco'      => 'required',
            'cta_banc_tipo'       => 'required',
            'cta_banc_moneda'     => 'required',
            'cta_banc_estado'     => 'required'
        ]);

        $cta_bancaria->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Cuenta bancaria modificada con éxito',
            'tipo'     => 'success',
            'registro' => $cta_bancaria
        ], 200);
    }

    public function destroy($id)
    {
        $cta_bancaria = Cta_bancarias::find($id);

        if (!$cta_bancaria) {
            return response()->json([
                'mensaje' => 'Cuenta bancaria no encontrada',
                'tipo'    => 'error',
                'registro' => $cta_bancaria
            ], 404);
        }

        $cta_bancaria->delete();

        return response()->json([
            'mensaje' => 'Cuenta bancaria eliminada con éxito',
            'tipo'    => 'success',
            'registro' => $cta_bancaria
        ], 200);
    }

    public function buscar(Request $r)
    {
        return DB::select("
            select *
            from cta_bancarias
            where cta_banc_banco ilike '%$r->cta_banc_banco%'
               or cta_banc_nro_cuenta ilike '%$r->cta_banc_banco%'
        ");
    }
}
