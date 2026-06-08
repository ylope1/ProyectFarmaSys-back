<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait VerificaPermisos
{
    public function tienePermiso($rol_id, $acc_ruta, $accion)
    {
        $accionesPermitidas = [
            'ver',
            'crear',
            'modificar',
            'anular',
            'confirmar',
            'aprobar',
            'rechazar',
            'imprimir'
        ];

        if (!in_array($accion, $accionesPermitidas)) {
            return false;
        }

        $permiso = DB::selectOne("
            SELECT 
                p.$accion AS permitido
            FROM permisos p
            JOIN accesos a ON a.id = p.acceso_id
            JOIN modulos m ON m.id = a.modulo_id
            WHERE p.rol_id = ?
              AND a.acc_ruta = ?
              AND a.acc_estado = 'ACTIVO'
              AND m.mod_estado = 'ACTIVO'
        ", [$rol_id, $acc_ruta]);

        if (!$permiso) {
            return false;
        }

        return (bool) $permiso->permitido;
    }

     public function verificarPermiso($acc_ruta, $accion)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->denegarPermiso('Usuario no autenticado');
        }

        if (!$this->tienePermiso($user->rol_id, $acc_ruta, $accion)) {
            return $this->denegarPermiso();
        }

        return null;
    }
    
    public function denegarPermiso($mensaje = 'No tiene permiso para realizar esta acción')
    {
        return response()->json([
            'mensaje' => $mensaje,
            'tipo' => 'error'
        ], 403);
    }
}