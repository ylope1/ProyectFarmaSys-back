<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Titulares;

class TitularesController extends Controller
{
    public function read()
    {
        return DB::select("
            select *
            from titulares
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'tit_nombre'   => 'required',
            'tit_apellido' => 'nullable',
            'tit_ci'       => 'nullable',
            'tit_direc'    => 'nullable',
            'tit_telef'    => 'nullable',
            'tit_email'    => 'nullable',
            'tit_estado'   => 'required'
        ]);

        $titular = Titulares::create($datosValidados);
        $titular->save();

        return response()->json([
            'mensaje'  => 'Titular registrado con éxito',
            'tipo'     => 'success',
            'registro' => $titular
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $titular = Titulares::find($id);

        if (!$titular) {
            return response()->json([
                'mensaje' => 'Titular no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'tit_nombre'   => 'required',
            'tit_apellido' => 'nullable',
            'tit_ci'       => 'nullable',
            'tit_direc'    => 'nullable',
            'tit_telef'    => 'nullable',
            'tit_email'    => 'nullable',
            'tit_estado'   => 'required'
        ]);

        $titular->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Titular modificado con éxito',
            'tipo'     => 'success',
            'registro' => $titular
        ], 200);
    }

    public function destroy($id)
    {
        $titular = Titulares::find($id);

        if (!$titular) {
            return response()->json([
                'mensaje' => 'Titular no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $titular->delete();

        return response()->json([
            'mensaje' => 'Titular eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    public function buscar(Request $r)
    {
        return DB::select("
            select *
            from titulares
            where tit_nombre ilike '%$r->tit_nombre%'
        ");
    }
}
