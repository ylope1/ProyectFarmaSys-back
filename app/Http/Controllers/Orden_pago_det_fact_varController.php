<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden_pago_cab;
use App\Models\Orden_pago_det_fact_var;
use Illuminate\Support\Facades\DB;

class Orden_pago_det_fact_varController extends Controller
{
    public function read($orden_pago_id)
    {
        return DB::select("
            SELECT 
                opd.orden_pago_id,
                opd.ctas_pagar_fact_varias_id,
                opd.op_cuota_nro,
                opd.op_monto_pagar,
                opd.op_saldo,
                to_char(opd.op_fecha_vto, 'dd/mm/yyyy') AS op_fecha_vto,
                fv.fact_var_fact AS documento,
                p.proveedor_desc
            FROM orden_pago_det_fact_var opd
            JOIN ctas_pagar_fact_varias cpfv 
                ON cpfv.id = opd.ctas_pagar_fact_varias_id
            JOIN facturas_varias_cab fv 
                ON fv.id = cpfv.factura_varia_id
            JOIN proveedores p 
                ON p.id = fv.proveedor_id
            WHERE opd.orden_pago_id = ?
            ORDER BY opd.op_fecha_vto
        ", [$orden_pago_id]);
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'orden_pago_id'                => 'required',
            'ctas_pagar_fact_varias_id'    => 'required',
            'op_cuota_nro'                 => 'required',
            'op_monto_pagar'               => 'required',
            'op_saldo'                     => 'required',
            'op_fecha_vto'                 => 'required'
        ]);

        // Evitar duplicados
        $existe = DB::table('orden_pago_det_fact_var')
            ->where('orden_pago_id', $request->orden_pago_id)
            ->where('ctas_pagar_fact_varias_id', $request->ctas_pagar_fact_varias_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'La cuota de la factura ya fue agregada a la orden de pago',
                'tipo'    => 'error'
            ], 422);
        }

        $detalle = Orden_pago_det_fact_var::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Detalle de factura varia agregado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function destroy($orden_pago_id, $ctas_pagar_fact_varias_id)
    {
        DB::table('orden_pago_det_fact_var')
            ->where('orden_pago_id', $orden_pago_id)
            ->where('ctas_pagar_fact_varias_id', $ctas_pagar_fact_varias_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Detalle eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }
}
