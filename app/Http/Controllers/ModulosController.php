<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Modulos;
use Illuminate\Support\Facades\DB;

class ModulosController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT 
                id,
                mod_desc,
                mod_orden,
                mod_estado,
                to_char(created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at,
                to_char(updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at
            FROM modulos
            ORDER BY mod_orden ASC, id ASC
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'mod_desc' => 'required|string|max:80',
            'mod_orden' => 'nullable|integer'
        ]);

        $modulo = Modulos::create([
            'mod_desc' => strtoupper($datosValidados['mod_desc']),
            'mod_orden' => $datosValidados['mod_orden'] ?? 0,
            'mod_estado' => 'ACTIVO'
        ]);

        return response()->json([
            'mensaje' => 'Módulo registrado correctamente',
            'tipo' => 'success',
            'registro' => $modulo
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $modulo = Modulos::find($id);

        if (!$modulo) {
            return response()->json([
                'mensaje' => 'El módulo no existe',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'mod_desc' => 'required|string|max:80',
            'mod_orden' => 'required|integer',
            'mod_estado' => 'required|string|max:20'
        ]);

        $modulo->update([
            'mod_desc' => strtoupper($datosValidados['mod_desc']),
            'mod_orden' => $datosValidados['mod_orden'],
            'mod_estado' => strtoupper($datosValidados['mod_estado'])
        ]);

        return response()->json([
            'mensaje' => 'Módulo actualizado correctamente',
            'tipo' => 'success',
            'registro' => $modulo
        ], 200);
    }

    public function anular($id)
    {
        $modulo = Modulos::find($id);

        if (!$modulo) {
            return response()->json([
                'mensaje' => 'El módulo no existe',
                'tipo' => 'error'
            ], 404);
        }

        $modulo->update([
            'mod_estado' => 'INACTIVO'
        ]);

        return response()->json([
            'mensaje' => 'Módulo inactivado correctamente',
            'tipo' => 'success',
            'registro' => $modulo
        ], 200);
    }

    public function buscar(Request $request)
    {
        $texto = strtoupper($request->texto ?? '');

        return DB::select("
            SELECT 
                id,
                mod_desc,
                mod_orden,
                mod_estado
            FROM modulos
            WHERE mod_estado = 'ACTIVO'
              AND UPPER(mod_desc) LIKE ?
            ORDER BY mod_orden ASC, mod_desc ASC
        ", ["%$texto%"]);
    }
}
