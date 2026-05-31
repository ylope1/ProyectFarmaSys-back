<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT 
                id,
                rol_desc,
                rol_abreviatura,
                rol_estado,
                to_char(created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at,
                to_char(updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at
            FROM roles
            ORDER BY id ASC
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'rol_desc' => 'required|string|max:50',
            'rol_abreviatura' => 'required|string|max:10',
        ]);

        $rol = Rol::create([
            'rol_desc' => strtoupper($datosValidados['rol_desc']),
            'rol_abreviatura' => strtoupper($datosValidados['rol_abreviatura']),
            'rol_estado' => 'ACTIVO'
        ]);

        return response()->json([
            'mensaje' => 'Rol registrado correctamente',
            'tipo' => 'success',
            'registro' => $rol
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::find($id);

        if (!$rol) {
            return response()->json([
                'mensaje' => 'El rol no existe',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'rol_desc' => 'required|string|max:50',
            'rol_abreviatura' => 'required|string|max:10',
            'rol_estado' => 'required|string|max:20'
        ]);

        $rol->update([
            'rol_desc' => strtoupper($datosValidados['rol_desc']),
            'rol_abreviatura' => strtoupper($datosValidados['rol_abreviatura']),
            'rol_estado' => strtoupper($datosValidados['rol_estado'])
        ]);

        return response()->json([
            'mensaje' => 'Rol actualizado correctamente',
            'tipo' => 'success',
            'registro' => $rol
        ], 200);
    }

    public function anular($id)
    {
        $rol = Rol::find($id);

        if (!$rol) {
            return response()->json([
                'mensaje' => 'El rol no existe',
                'tipo' => 'error'
            ], 404);
        }

        $rol->update([
            'rol_estado' => 'INACTIVO'
        ]);

        return response()->json([
            'mensaje' => 'Rol inactivado correctamente',
            'tipo' => 'success',
            'registro' => $rol
        ], 200);
    }

    public function buscar(Request $request)
    {
        $texto = strtoupper($request->texto ?? '');

        return DB::select("
            SELECT 
                id,
                rol_desc,
                rol_abreviatura,
                rol_estado
            FROM roles
            WHERE rol_estado = 'ACTIVO'
              AND (
                    UPPER(rol_desc) LIKE ?
                    OR UPPER(rol_abreviatura) LIKE ?
              )
            ORDER BY rol_desc ASC
        ", ["%$texto%", "%$texto%"]);
    }
}
