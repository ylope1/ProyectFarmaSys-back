<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accesos;
use Illuminate\Support\Facades\DB;

class AccesosController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT 
                a.id,
                a.modulo_id,
                m.mod_desc,
                a.acc_desc,
                a.acc_ruta,
                a.acc_orden,
                a.acc_estado,
                to_char(a.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at,
                to_char(a.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at
            FROM accesos a
            JOIN modulos m ON m.id = a.modulo_id
            ORDER BY m.mod_orden ASC, a.acc_orden ASC, a.id ASC
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'modulo_id' => 'required|exists:modulos,id',
            'acc_desc' => 'required|string|max:100',
            'acc_ruta' => 'required|string|max:255',
            'acc_orden' => 'nullable|integer'
        ]);

        $acceso = Accesos::create([
            'modulo_id' => $datosValidados['modulo_id'],
            'acc_desc' => strtoupper($datosValidados['acc_desc']),
            'acc_ruta' => $datosValidados['acc_ruta'],
            'acc_orden' => $datosValidados['acc_orden'] ?? 0,
            'acc_estado' => 'ACTIVO'
        ]);

        return response()->json([
            'mensaje' => 'Acceso registrado correctamente',
            'tipo' => 'success',
            'registro' => $acceso
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $acceso = Accesos::find($id);

        if (!$acceso) {
            return response()->json([
                'mensaje' => 'El acceso no existe',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'modulo_id' => 'required|exists:modulos,id',
            'acc_desc' => 'required|string|max:100',
            'acc_ruta' => 'required|string|max:255',
            'acc_orden' => 'required|integer',
            'acc_estado' => 'required|string|max:20'
        ]);

        $acceso->update([
            'modulo_id' => $datosValidados['modulo_id'],
            'acc_desc' => strtoupper($datosValidados['acc_desc']),
            'acc_ruta' => $datosValidados['acc_ruta'],
            'acc_orden' => $datosValidados['acc_orden'],
            'acc_estado' => strtoupper($datosValidados['acc_estado'])
        ]);

        return response()->json([
            'mensaje' => 'Acceso actualizado correctamente',
            'tipo' => 'success',
            'registro' => $acceso
        ], 200);
    }

    public function anular($id)
    {
        $acceso = Accesos::find($id);

        if (!$acceso) {
            return response()->json([
                'mensaje' => 'El acceso no existe',
                'tipo' => 'error'
            ], 404);
        }

        $acceso->update([
            'acc_estado' => 'INACTIVO'
        ]);

        return response()->json([
            'mensaje' => 'Acceso inactivado correctamente',
            'tipo' => 'success',
            'registro' => $acceso
        ], 200);
    }

    public function buscar(Request $request)
    {
        $texto = strtoupper($request->texto ?? '');

        return DB::select("
            SELECT 
                a.id,
                a.modulo_id,
                m.mod_desc,
                a.acc_desc,
                a.acc_ruta,
                a.acc_orden,
                a.acc_estado
            FROM accesos a
            JOIN modulos m ON m.id = a.modulo_id
            WHERE a.acc_estado = 'ACTIVO'
              AND (
                    UPPER(a.acc_desc) LIKE ?
                    OR UPPER(m.mod_desc) LIKE ?
                    OR UPPER(a.acc_ruta) LIKE ?
              )
            ORDER BY m.mod_orden ASC, a.acc_orden ASC, a.acc_desc ASC
        ", ["%$texto%", "%$texto%", "%$texto%"]);
    }

    public function buscarPorModulo($modulo_id)
    {
        return DB::select("
            SELECT 
                id,
                modulo_id,
                acc_desc,
                acc_ruta,
                acc_orden,
                acc_estado
            FROM accesos
            WHERE modulo_id = ?
              AND acc_estado = 'ACTIVO'
            ORDER BY acc_orden ASC, acc_desc ASC
        ", [$modulo_id]);
    }
}
