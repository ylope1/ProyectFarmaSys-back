<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Rendicion_ff_cab;
use App\Models\Asignacion_fondo_fijo;

class Rendicion_ff_cab_Controller extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                rfc.*,
                to_char(rfc.rendicion_ff_fecha, 'dd/mm/yyyy HH24:mi:ss') AS rendicion_ff_fecha,
                u.name AS encargado,
                p.proveedor_desc AS responsable,
                e.empresa_desc,
                s.suc_desc,
                aff.asignacion_ff_monto,
                aff.asignacion_ff_estado
            FROM rendicion_ff_cab rfc
            JOIN asignacion_fondo_fijo aff ON aff.id = rfc.asignacion_ff_id
            JOIN users u ON u.id = rfc.user_id
            JOIN proveedores p ON p.id = aff.proveedor_id
            JOIN empresas e ON e.id = rfc.empresa_id
            JOIN sucursales s ON s.id = rfc.sucursal_id
            ORDER BY rfc.id DESC
        ");
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'asignacion_ff_id'        => 'required|exists:asignacion_fondo_fijo,id',
            'user_id'                 => 'required|exists:users,id',
            'empresa_id'              => 'required|exists:empresas,id',
            'sucursal_id'             => 'required|exists:sucursales,id',
            'rendicion_ff_fecha'      => 'required',
            'rendicion_ff_monto_gral' => 'required|numeric|min:1'
        ]);

        // Verificar asignación activa
        $asignacion = Asignacion_fondo_fijo::find($request->asignacion_ff_id);

        if (!$asignacion || $asignacion->asignacion_ff_estado !== 'ACTIVO') {
            return response()->json([
                'mensaje' => 'La asignación debe estar en estado ACTIVO para rendir.',
                'tipo' => 'error'
            ], 400);
        }

        $datos['rendicion_ff_estado'] = 'REGISTRADO';

        $rendicion = Rendicion_ff_cab::create($datos);

        return response()->json([
            'mensaje'  => 'Rendición registrada correctamente.',
            'tipo'     => 'success',
            'registro' => $rendicion
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $rendicion = Rendicion_ff_cab::find($id);

        if (!$rendicion) {
            return response()->json([
                'mensaje' => 'Registro no encontrado.',
                'tipo' => 'error'
            ], 404);
        }

        if ($rendicion->rendicion_ff_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden modificar rendiciones en estado REGISTRADO.',
                'tipo' => 'error'
            ], 400);
        }

        $datos = $request->validate([
            'rendicion_ff_fecha'      => 'required',
            'rendicion_ff_monto_gral' => 'required|numeric|min:1'
        ]);

        $rendicion->update($datos);

        return response()->json([
            'mensaje'  => 'Rendición modificada correctamente.',
            'tipo'     => 'success',
            'registro' => $rendicion
        ], 200);
    }

    public function anular($id)
    {
        $rendicion = Rendicion_ff_cab::find($id);

        if (!$rendicion) {
            return response()->json([
                'mensaje' => 'Registro no encontrado.',
                'tipo' => 'error'
            ], 404);
        }

        if ($rendicion->rendicion_ff_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden anular rendiciones en estado REGISTRADO.',
                'tipo' => 'error'
            ], 400);
        }

        $rendicion->rendicion_ff_estado = 'ANULADO';
        $rendicion->save();

        return response()->json([
            'mensaje'  => 'Rendición anulada correctamente.',
            'tipo'     => 'success',
            'registro' => $rendicion
        ], 200);
    }

    public function confirmar($id)
    {
        $rendicion = Rendicion_ff_cab::find($id);

        if (!$rendicion) {
            return response()->json([
                'mensaje' => 'Rendición no encontrada.',
                'tipo' => 'error'
            ], 404);
        }

        if ($rendicion->rendicion_ff_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar rendiciones en estado REGISTRADO.',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();
        try {

            // Total detalle rendido
            $totalDetalle = DB::table('rendicion_ff_det')
                ->where('rendicion_ff_id', $rendicion->id)
                ->sum('rendicion_ff_det_monto');

            if ($totalDetalle <= 0) {
                throw new \Exception('La rendición no posee documentos.');
            }

            // Saldo disponible del fondo fijo
            $saldoDisponible = DB::table('ctas_pagar_fondo_fijo')
                ->where('asignacion_ff_id', $rendicion->asignacion_ff_id)
                ->sum('ctas_pagar_ff_saldo');

            if ($totalDetalle > $saldoDisponible) {
                throw new \Exception('El monto rendido supera el saldo disponible del fondo fijo.');
            }

            // Validar documentos no rendidos previamente
            $duplicados = DB::select("
                SELECT documento_id
                FROM rendicion_ff_det
                WHERE documento_id IN (
                    SELECT documento_id
                    FROM rendicion_ff_det
                    WHERE rendicion_ff_id = ?
                )
                AND rendicion_ff_id <> ?
            ", [$rendicion->id, $rendicion->id]);

            if (count($duplicados) > 0) {
                throw new \Exception('Existen documentos ya rendidos previamente.');
            }

            // Confirmar rendición
            $rendicion->rendicion_ff_estado = 'CONFIRMADO';
            $rendicion->rendicion_ff_monto_gral = $totalDetalle;
            $rendicion->save();

            DB::commit();

            return response()->json([
                'mensaje'  => 'Rendición confirmada correctamente.',
                'tipo'     => 'success',
                'registro' => $rendicion
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al confirmar la rendición.',
                'error'   => $e->getMessage(),
                'tipo'    => 'error'
            ], 500);
        }
    }

}
