<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden_pago_cab;
use App\Models\Orden_pago_det;
use App\Models\Orden_pago_det_fact_var;
use App\Models\Ctas_pagar;
use App\Models\Ctas_pagar_fact_varias;
use Illuminate\Support\Facades\DB;

class Orden_pago_cabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT 
                opc.*,
                to_char(opc.orden_pago_fec, 'dd/mm/yyyy') as orden_pago_fec,
                to_char(opc.orden_pago_fec_aprob, 'dd/mm/yyyy') as orden_pago_fec_aprob,
                p.proveedor_desc,
                e.empresa_desc,
                s.suc_desc,
                u.name as encargado,
                fc.forma_cob_desc
            FROM orden_pago_cab opc
            JOIN proveedores p ON p.id = opc.proveedor_id
            JOIN empresas e ON e.id = opc.empresa_id
            JOIN sucursales s ON s.id = opc.sucursal_id
            JOIN users u ON u.id = opc.user_id
            JOIN forma_cobros fc ON fc.id = opc.forma_cobro_id
            ORDER BY opc.id DESC
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'empresa_id'          => 'required',
            'sucursal_id'         => 'required',
            'proveedor_id'        => 'required',
            'user_id'             => 'required',
            'forma_cobro_id'      => 'required',
            'orden_pago_fec'      => 'required',
            'orden_pago_estado'   => 'required'
        ]);

        $orden = Orden_pago_cab::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Orden de pago registrada con éxito',
            'tipo'     => 'success',
            'registro' => $orden
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $orden = Orden_pago_cab::find($id);
        if (!$orden) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($orden->orden_pago_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden modificar órdenes en estado PENDIENTE',
                'tipo'    => 'error'
            ], 422);
        }

        $datosValidados = $request->validate([
            'empresa_id'          => 'required',
            'sucursal_id'         => 'required',
            'proveedor_id'        => 'required',
            'user_id'             => 'required',
            'forma_cobro_id'      => 'required',
            'orden_pago_fec'      => 'required'
        ]);

        $orden->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $orden
        ], 200);
    }

     public function anular(Request $request, $id)
    {
        $orden = Orden_pago_cab::find($id);
        if (!$orden) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $orden->orden_pago_estado = 'ANULADO';
        $orden->user_id = $request->user_id; // quién anuló
        $orden->save();

        return response()->json([
            'mensaje'  => 'Orden de pago anulada con éxito',
            'tipo'     => 'success',
            'registro' => $orden
        ], 200);
    }

    public function aprobar(Request $request, $id)
    {
        $orden = Orden_pago_cab::find($id);
        if (!$orden) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($orden->orden_pago_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden aprobar órdenes en estado PENDIENTE',
                'tipo'    => 'error'
            ], 422);
        }

        $request->validate([
            'orden_pago_fec_aprob' => 'required',
            'user_id'              => 'required'
        ]);

        $orden->orden_pago_fec_aprob = $request->orden_pago_fec_aprob;
        $orden->orden_pago_estado = 'APROBADO';
        $orden->user_id = $request->user_id;

        $orden->save();

        return response()->json([
            'mensaje'  => 'Orden de pago aprobada con éxito',
            'tipo'     => 'success',
            'registro' => $orden
        ], 200);
    }    

    public function rechazar(Request $request, $id)
    {
        $orden = Orden_pago_cab::find($id);
        if (!$orden) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($orden->orden_pago_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden rechazar órdenes en estado PENDIENTE',
                'tipo'    => 'error'
            ], 422);
        }

        $orden->orden_pago_estado = 'RECHAZADO';
        $orden->user_id = $request->user_id;
        $orden->save();

        return response()->json([
            'mensaje'  => 'Orden de pago rechazada',
            'tipo'     => 'success',
            'registro' => $orden
        ], 200);
    }

    public function confirmar(Request $request, $id)
    {
        $orden = Orden_pago_cab::find($id);

        if (!$orden) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        // Solo se puede confirmar una orden APROBADA
        if ($orden->orden_pago_estado !== 'APROBADO') {
            return response()->json([
                'mensaje' => 'La orden debe estar APROBADA para poder confirmarse',
                'tipo'    => 'error'
            ], 422);
        }

        // Validación debe tener fecha de aprobación
        if (!$orden->orden_pago_fec_aprob) {
            return response()->json([
                'mensaje' => 'La orden no tiene fecha de aprobación',
                'tipo'    => 'error'
            ], 422);
        }

        $orden->orden_pago_estado = 'CONFIRMADO';
        $orden->user_id = $request->user_id;
        $orden->save();

        return response()->json([
            'mensaje'  => 'Orden de pago confirmada con éxito',
            'tipo'     => 'success',
            'registro' => $orden
        ], 200);
    }
 

    public function buscar(Request $r)
    {
        return DB::select("
            SELECT 
                opc.id,
                opc.orden_pago_estado,
                to_char(opc.orden_pago_fec_aprob, 'dd/mm/yyyy') as fecha_aprob,
                p.proveedor_desc,
                'ORDEN PAGO NRO: ' || to_char(opc.id, '0000000') ||
                ' (' || opc.orden_pago_estado || ')' as orden
            FROM orden_pago_cab opc
            JOIN proveedores p ON p.id = opc.proveedor_id
            WHERE opc.orden_pago_estado = 'CONFIRMADO'
              AND opc.user_id = {$r->user_id}
              AND p.proveedor_desc ILIKE '%{$r->proveedor}%'
        ");
    }

    public function buscarCuotasPendientesProveedor(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required'
        ]);

        $proveedor_id = $request->proveedor_id;

        return DB::select("
            SELECT 
                'COMPRA' AS tipo_documento,
                cp.id AS ctas_pagar_id,
                cp.compra_id,
                c.compra_fact AS documento,
                cp.nro_cuota AS cuota_nro,
                to_char(cp.fecha_vencimiento, 'DD/MM/YYYY') AS fecha_vto,
                cp.fecha_vencimiento AS fecha_vto_orden,
                cp.monto AS monto,
                cp.saldo AS saldo,
                NULL::bigint AS ctas_pagar_fact_varias_id
            FROM ctas_pagar cp
            JOIN compras_cab c ON c.id = cp.compra_id
            WHERE c.proveedor_id = ?
            AND cp.estado = 'PENDIENTE'
            AND cp.saldo > 0

            UNION ALL

            SELECT 
                'FACT_VAR' AS tipo_documento,
                NULL::bigint AS ctas_pagar_id,
                NULL::bigint AS compra_id,
                fv.fact_var_fact AS documento,
                cpfv.cta_pagar_fv_nro_cuota AS cuota_nro,
                to_char(cpfv.cta_pagar_fv_fec_vto, 'DD/MM/YYYY') AS fecha_vto,
                cpfv.cta_pagar_fv_fec_vto AS fecha_vto_orden,
                cpfv.cta_pagar_fv_monto AS monto,
                cpfv.cta_pagar_fv_saldo AS saldo,
                cpfv.id AS ctas_pagar_fact_varias_id
            FROM ctas_pagar_fact_varias cpfv
            JOIN facturas_varias_cab fv ON fv.id = cpfv.factura_varia_id
            WHERE fv.proveedor_id = ?
            AND cpfv.cta_pagar_fv_estado = 'PENDIENTE'
            AND cpfv.cta_pagar_fv_saldo > 0

            ORDER BY fecha_vto_orden
        ", [$proveedor_id, $proveedor_id]);
    }

}
