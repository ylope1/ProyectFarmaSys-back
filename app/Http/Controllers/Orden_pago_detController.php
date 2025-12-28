<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden_pago_cab;
use App\Models\Orden_pago_det;
use Illuminate\Support\Facades\DB;

class Orden_pago_detController extends Controller
{
    public function read($orden_pago_id)
    {
        return DB::select("
            SELECT 
                opd.orden_pago_id,
                opd.ctas_pagar_id,
                opd.compra_id,
                opd.op_cuota_nro,
                opd.op_monto_pagar,
                opd.op_saldo,
                to_char(opd.op_fecha_vto, 'dd/mm/yyyy') as op_fecha_vto,
                c.compra_fact,
                p.proveedor_desc
            FROM orden_pago_det opd
            JOIN ctas_pagar cp 
                ON cp.id = opd.ctas_pagar_id 
               AND cp.compra_id = opd.compra_id
            JOIN compras_cab c 
                ON c.id = opd.compra_id
            JOIN proveedores p 
                ON p.id = c.proveedor_id
            WHERE opd.orden_pago_id = ?
            ORDER BY opd.op_fecha_vto
        ", [$orden_pago_id]);
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'orden_pago_id' => 'required',
            'ctas_pagar_id' => 'required',
            'compra_id'     => 'required',
            'op_cuota_nro'  => 'required',
            'op_monto_pagar'=> 'required',
            'op_saldo'      => 'required',
            'op_fecha_vto'  => 'required'
        ]);

        // Evitar duplicados
        $existe = DB::table('orden_pago_det')
            ->where('orden_pago_id', $request->orden_pago_id)
            ->where('ctas_pagar_id', $request->ctas_pagar_id)
            ->where('compra_id', $request->compra_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'La cuota ya fue agregada a la orden de pago',
                'tipo'    => 'error'
            ], 422);
        }

        $detalle = Orden_pago_det::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Detalle agregado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function destroy($orden_pago_id, $ctas_pagar_id, $compra_id)
    {
        DB::table('orden_pago_det')
            ->where('orden_pago_id', $orden_pago_id)
            ->where('ctas_pagar_id', $ctas_pagar_id)
            ->where('compra_id', $compra_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Detalle eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }
}
