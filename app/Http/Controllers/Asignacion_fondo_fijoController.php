<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Asignacion_fondo_fijo;

class Asignacion_fondo_fijoController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                aff.*,
                u.name AS encargado,
                p.proveedor_desc AS responsable,
                e.empresa_desc,
                s.suc_desc,
                to_char(aff.asignacion_ff_fecha, 'dd/mm/yyyy HH24:mi:ss') AS asignacion_ff_fecha
            FROM asignacion_fondo_fijo aff
            JOIN users u ON u.id = aff.user_id
            JOIN proveedores p ON p.id = aff.proveedor_id
            JOIN empresas e ON e.id = aff.empresa_id
            JOIN sucursales s ON s.id = aff.sucursal_id
        ");
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'user_id'             => 'required|exists:users,id',
            'proveedor_id'        => 'required|exists:proveedores,id',
            'empresa_id'          => 'required|exists:empresas,id',
            'sucursal_id'         => 'required|exists:sucursales,id',
            'asignacion_ff_monto' => 'required|numeric|min:1',
            'asignacion_ff_fecha' => 'required',
            'asignacion_ff_obs'   => 'nullable|string'
        ]);

        // Verificar que no exista otro fondo fijo ACTIVO o APROBADO para el mismo responsable
        $existe = Asignacion_fondo_fijo::where('proveedor_id', $request->proveedor_id)
            ->whereIn('asignacion_ff_estado', ['GENERADO','ACTIVO'])
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'El responsable ya tiene un fondo fijo activo o pendiente.',
                'tipo' => 'error'
            ], 400);
        }

        $datos['asignacion_ff_estado'] = 'GENERADO';

        $asignacion = Asignacion_fondo_fijo::create($datos);

        return response()->json([
            'mensaje' => 'Asignación de fondo fijo registrada correctamente.',
            'tipo' => 'success',
            'registro' => $asignacion
        ], 200);
    }

    public function confirmar($id)
    {
        $asignacion = Asignacion_fondo_fijo::find($id);

        if (!$asignacion || $asignacion->asignacion_ff_estado !== 'GENERADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar asignaciones en estado GENERADO.',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();
        try {

            // Obtener correlativo por asignación
            $nextId = Ctas_pagar_fondo_fijo::where('asignacion_ff_id', $asignacion->id)
                ->max('id');

            $nextId = ($nextId ?? 0) + 1;

            // Insertar PROVISIÓN inicial del fondo fijo
            Ctas_pagar_fondo_fijo::create([
                'id'                      => $nextId,
                'asignacion_ff_id'        => $asignacion->id,
                'rendicion_ff_id'         => null,
                'ctas_pagar_ff_monto'     => $asignacion->asignacion_ff_monto,
                'ctas_pagar_ff_saldo'     => $asignacion->asignacion_ff_monto,
                'ctas_pagar_ff_fec_vto'   => $asignacion->asignacion_ff_fec_vto,
                'ctas_pagar_ff_nro_cuota' => 1,
                'ctas_pagar_ff_estado'    => 'PENDIENTE',
                'ctas_pagar_ff_tipo'      => 'PROVISION'
            ]);

            // Activar el fondo fijo
            $asignacion->asignacion_ff_estado = 'ACTIVO';
            $asignacion->save();

            DB::commit();

            return response()->json([
                'mensaje' => 'Fondo fijo activado y cuenta a pagar generada.',
                'tipo' => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al confirmar la asignación.',
                'error'   => $e->getMessage(),
                'tipo'    => 'error'
            ], 500);
        }
    }

    public function inactivar($id)
    {
        $asignacion = Asignacion_fondo_fijo::find($id);

        if (!$asignacion || $asignacion->asignacion_ff_estado !== 'ACTIVO') {
            return response()->json([
                'mensaje' => 'Solo se pueden inactivar fondos en estado ACTIVO.',
                'tipo' => 'error'
            ], 400);
        }

        $asignacion->asignacion_ff_estado = 'INACTIVO';
        $asignacion->save();

        return response()->json([
            'mensaje' => 'Fondo fijo inactivado.',
            'tipo' => 'success',
            'registro' => $asignacion
        ], 200);
    }

    // Re-activar un fondo fijo inactivo
    public function activar($id)
    {
        $asignacion = Asignacion_fondo_fijo::find($id);

        if (!$asignacion || $asignacion->asignacion_ff_estado !== 'INACTIVO') {
            return response()->json([
                'mensaje' => 'Solo se pueden activar fondos en estado INACTIVO.',
                'tipo' => 'error'
            ], 400);
        }

        $asignacion->asignacion_ff_estado = 'ACTIVO';
        $asignacion->save();

        return response()->json([
            'mensaje' => 'Fondo fijo reactivado.',
            'tipo' => 'success',
            'registro' => $asignacion
        ], 200);
    }

    public function cerrar($id)
    {
        $asignacion = Asignacion_fondo_fijo::find($id);

        if (!$asignacion || !in_array($asignacion->asignacion_ff_estado, ['ACTIVO','INACTIVO'])) {
            return response()->json([
                'mensaje' => 'Solo se pueden cerrar fondos activos o inactivos.',
                'tipo' => 'error'
            ], 400);
        }

        // Reglas futuras:
        // - no debe haber rendiciones pendientes
        // - saldo del fondo debe ser 0

        $asignacion->asignacion_ff_estado = 'CERRADO';
        $asignacion->save();

        return response()->json([
            'mensaje' => 'Fondo fijo cerrado correctamente.',
            'tipo' => 'success',
            'registro' => $asignacion
        ], 200);
    }

}
