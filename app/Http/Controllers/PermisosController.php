<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Permisos;
use App\Traits\VerificaPermisos; 

class PermisosController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'referenciales/seguridad/permisos/';

    public function read()
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("
            SELECT 
                p.rol_id,
                r.rol_desc,
                p.acceso_id,
                a.acc_desc,
                a.acc_ruta,
                m.id as modulo_id,
                m.mod_desc,

                p.ver,
                p.crear,
                p.modificar,
                p.anular,
                p.confirmar,
                p.aprobar,
                p.rechazar,
                p.imprimir,

                to_char(p.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at,
                to_char(p.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at
            FROM permisos p
            JOIN roles r ON r.id = p.rol_id
            JOIN accesos a ON a.id = p.acceso_id
            JOIN modulos m ON m.id = a.modulo_id
            ORDER BY r.rol_desc ASC, m.mod_orden ASC, a.acc_orden ASC
        ");
    }

    public function buscarPorRol($rol_id)
    {
        return DB::select("
            SELECT 
                a.id as acceso_id,
                a.acc_desc,
                a.acc_ruta,
                a.acc_orden,
                m.id as modulo_id,
                m.mod_desc,
                m.mod_orden,

                COALESCE(p.ver, false) as ver,
                COALESCE(p.crear, false) as crear,
                COALESCE(p.modificar, false) as modificar,
                COALESCE(p.anular, false) as anular,
                COALESCE(p.confirmar, false) as confirmar,
                COALESCE(p.aprobar, false) as aprobar,
                COALESCE(p.rechazar, false) as rechazar,
                COALESCE(p.imprimir, false) as imprimir
            FROM accesos a
            JOIN modulos m ON m.id = a.modulo_id
            LEFT JOIN permisos p 
                ON p.acceso_id = a.id 
                AND p.rol_id = ?
            WHERE a.acc_estado = 'ACTIVO'
              AND m.mod_estado = 'ACTIVO'
            ORDER BY m.mod_orden ASC, a.acc_orden ASC, a.acc_desc ASC
        ", [$rol_id]);
    }

    public function store(Request $request)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }
        
        $request->validate([
            'rol_id' => 'required|exists:roles,id',
            'permisos' => 'required|array'
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->permisos as $permiso) {
                if (!isset($permiso['acceso_id'])) {
                    continue;
                }

                DB::table('permisos')->updateOrInsert(
                    [
                        'rol_id' => $request->rol_id,
                        'acceso_id' => $permiso['acceso_id']
                    ],
                    [
                        'ver' => $permiso['ver'] ?? false,
                        'crear' => $permiso['crear'] ?? false,
                        'modificar' => $permiso['modificar'] ?? false,
                        'anular' => $permiso['anular'] ?? false,
                        'confirmar' => $permiso['confirmar'] ?? false,
                        'aprobar' => $permiso['aprobar'] ?? false,
                        'rechazar' => $permiso['rechazar'] ?? false,
                        'imprimir' => $permiso['imprimir'] ?? false,
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Permisos registrados correctamente',
                'tipo' => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al registrar permisos',
                'tipo' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verificarPermiso(Request $request)
    {
        $request->validate([
            'rol_id' => 'required|exists:roles,id',
            'acc_ruta' => 'required|string',
            'accion' => 'required|string'
        ]);

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

        if (!in_array($request->accion, $accionesPermitidas)) {
            return response()->json([
                'permitido' => false,
                'mensaje' => 'Acción no válida'
            ], 400);
        }

        $permiso = DB::selectOne("
            SELECT 
                p.*
            FROM permisos p
            JOIN accesos a ON a.id = p.acceso_id
            WHERE p.rol_id = ?
              AND a.acc_ruta = ?
              AND a.acc_estado = 'ACTIVO'
        ", [$request->rol_id, $request->acc_ruta]);

        if (!$permiso) {
            return response()->json([
                'permitido' => false,
                'mensaje' => 'No posee permiso para este acceso'
            ], 403);
        }

        $accion = $request->accion;

        return response()->json([
            'permitido' => (bool) $permiso->$accion
        ], 200);
    }
}
