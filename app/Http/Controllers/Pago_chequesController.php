<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pago_cheques;
use App\Models\Mov_bancarios;
use App\Models\Orden_pago_cab;

class Pago_chequesController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                pc.orden_pago_id,
                pc.mov_bancario_id,
                pc.fecha_entrega,
                pc.pag_cheq_estado,
                pc.retira_nombre,
                pc.retira_ci,
                pc.retira_telefono,

                -- ORDEN DE PAGO
                opc.orden_pago_estado,
                'ORDEN PAGO NRO: ' || to_char(opc.id, '0000000') AS orden_pago_desc,

                -- EMPRESA / SUCURSAL / PROVEEDOR
                e.id  AS empresa_id,
                e.empresa_desc,
                s.id  AS sucursal_id,
                s.suc_desc,
                p.id  AS proveedor_id,
                p.proveedor_desc,

                -- MOVIMIENTO BANCARIO
                mb.cta_bancaria_id,
                cb.cta_banc_banco || ' - ' || cb.cta_banc_nro_cuenta AS cta_banc_desc,
                mb.titular_id,
                t.tit_nombre || ' ' || COALESCE(t.tit_apellido,'') AS titular_desc,
                mb.mov_banc_nro_ref,
                to_char(mb.mov_banc_fec_emision,'DD/MM/YYYY') AS mov_banc_fec_emision,
                to_char(mb.mov_banc_fec_valor,'DD/MM/YYYY')   AS mov_banc_fec_valor,
                mb.mov_banc_monto_debito AS mov_banc_monto,
                mb.observacion

            FROM pago_cheques pc
            JOIN mov_bancarios mb     ON mb.id = pc.mov_bancario_id
            JOIN orden_pago_cab opc   ON opc.id = pc.orden_pago_id
            JOIN proveedores p        ON p.id = opc.proveedor_id
            JOIN empresas e           ON e.id = opc.empresa_id
            JOIN sucursales s         ON s.id = opc.sucursal_id
            JOIN cta_bancarias cb     ON cb.id = mb.cta_bancaria_id
            JOIN titulares t          ON t.id = mb.titular_id

            ORDER BY pc.fecha_entrega DESC
        ");
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            // 1️⃣ Validar orden
            $orden = Orden_pago_cab::find($request->orden_pago_id);

            if (!$orden || $orden->orden_pago_estado !== 'CONFIRMADO') {
                return response()->json([
                    'mensaje' => 'Orden de pago inválida',
                    'tipo' => 'error'
                ], 400);
            }

            // 2️⃣ Crear MOVIMIENTO BANCARIO (REGISTRADO)
            $mov = Mov_bancarios::create([
                'cta_bancaria_id'        => $request->cta_bancaria_id,
                'titular_id'             => $request->titular_id,
                'mov_banc_fecha'         => $request->mov_banc_fecha,
                'mov_banc_tipo'          => 'CHEQUE',
                'mov_banc_nro_ref'       => $request->mov_banc_nro_ref,
                'mov_banc_fec_emision'   => $request->mov_banc_fec_emision,
                'mov_banc_fec_valor'     => $request->mov_banc_fec_valor,
                'mov_banc_monto_debito'  => $request->mov_banc_monto_debito,
                'mov_banc_monto_credito' => 0,
                'mov_banc_estado'        => 'REGISTRADO',
                'sucursal_id'            => $request->sucursal_id,
                'user_id'                => $request->user_id,
                'observacion'            => $request->observacion
            ]);

            // 3️⃣ Crear PAGO CHEQUE (REGISTRADO)
            Pago_cheques::create([
                'orden_pago_id'   => $orden->id,
                'mov_bancario_id' => $mov->id,
                'retira_nombre'   => $request->retira_nombre,
                'retira_ci'       => $request->retira_ci,
                'retira_telefono' => $request->retira_telefono,
                'fecha_entrega'   => $request->fecha_entrega,
                'pag_cheq_estado' => 'REGISTRADO'
            ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Entrega de cheque registrada correctamente',
                'tipo'    => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al registrar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function anular($orden_pago_id, $mov_bancario_id)
    {
        DB::beginTransaction();

        try {

            // 1 Verificar existencia del registro
            $registro = Pago_cheques::where('orden_pago_id', $orden_pago_id)
                ->where('mov_bancario_id', $mov_bancario_id)
                ->first();

            if (!$registro) {
                return response()->json([
                    'mensaje' => 'Entrega de cheque no encontrada',
                    'tipo'    => 'error'
                ], 404);
            }

            if ($registro->pag_cheq_estado !== 'REGISTRADO') {
                return response()->json([
                    'mensaje' => 'Solo se pueden anular registros en estado REGISTRADO',
                    'tipo'    => 'error'
                ], 400);
            }

            // 2 Anular entrega de cheque (SIN save)
            Pago_cheques::where('orden_pago_id', $orden_pago_id)
                ->where('mov_bancario_id', $mov_bancario_id)
                ->update([
                    'pag_cheq_estado' => 'ANULADO'
                ]);

            // 3 Anular movimiento bancario
            Mov_bancarios::where('id', $mov_bancario_id)
                ->update([
                    'mov_banc_estado' => 'ANULADO'
                ]);

            // 4 Restaurar orden de pago a APROBADO
            Orden_pago_cab::where('id', $orden_pago_id)
                ->update([
                    'orden_pago_estado' => 'APROBADO'
                ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Entrega de cheque anulada correctamente',
                'tipo'    => 'success'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al anular entrega de cheque',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function confirmar($orden_pago_id, $mov_bancario_id)
    {
        DB::beginTransaction();

        try {

            $registro = Pago_cheques::where('orden_pago_id', $orden_pago_id)
                ->where('mov_bancario_id', $mov_bancario_id)
                ->first();

            if (!$registro) {
                return response()->json([
                    'mensaje' => 'Entrega de cheque no encontrada',
                    'tipo'    => 'error'
                ], 404);
            }

            if ($registro->pag_cheq_estado !== 'REGISTRADO') {
                return response()->json([
                    'mensaje' => 'El registro no se encuentra en estado REGISTRADO',
                    'tipo'    => 'error'
                ], 400);
            }

            // Confirmar entrega
            Pago_cheques::where('orden_pago_id', $orden_pago_id)
            ->where('mov_bancario_id', $mov_bancario_id)
            ->update([
                'pag_cheq_estado' => 'ENTREGADO'
            ]);

            // Confirmar movimiento bancario
            Mov_bancarios::where('id', $mov_bancario_id)
            ->update([
                'mov_banc_estado' => 'CONFIRMADO'
            ]);

            // Marcar orden de pago como PAGADO
            $orden = Orden_pago_cab::find($orden_pago_id);
            if ($orden) {
                $orden->orden_pago_estado = 'PAGADO';
                $orden->save();
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Entrega de cheque confirmada correctamente',
                'tipo'    => 'success'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al confirmar entrega de cheque',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function buscar(Request $r)
    {
        return DB::select("
            select
                pc.orden_pago_id,
                pc.mov_bancario_id,
                pc.fecha_entrega,
                pc.pag_cheq_estado,
                mb.mov_banc_nro_ref,
                mb.mov_banc_monto_debito
            from pago_cheques pc
            join mov_bancarios mb on mb.id = pc.mov_bancario_id
            where pc.orden_pago_id = ?
        ", [$r->orden_pago_id]);
    }
}
