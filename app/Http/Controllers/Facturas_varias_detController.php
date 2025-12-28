<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Facturas_varias_det;

class Facturas_varias_detController extends Controller
{
    public function read($factura_varia_id)
    {
        return DB::select("
            SELECT 
                fvd.*,
                r.rubro_desc
            FROM facturas_varias_det fvd
            JOIN rubros r ON r.id = fvd.rubro_id
            WHERE fvd.factura_varia_id = ?
        ", [$factura_varia_id]);
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'factura_varia_id' => 'required|exists:facturas_varias_cab,id',
            'rubro_id'         => 'required|exists:rubros,id',
            'fact_var_cant'    => 'required|numeric|min:1',
            'fact_var_monto'   => 'required|numeric|min:0',
            'fact_var_tipo_iva' => 'required|in:EXENTA,5,10'
        ]);

        $detalle = Facturas_varias_det::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function update(Request $request, $factura_varia_id, $rubro_id)
    {
        $datosValidados = $request->validate([
            'fact_var_cant'  => 'required|numeric|min:1',
            'fact_var_monto' => 'required|numeric|min:0',
            'fact_var_tipo_iva' => 'required|in:EXENTA,5,10'
        ]);

        DB::table('facturas_varias_det')
            ->where('factura_varia_id', $factura_varia_id)
            ->where('rubro_id', $rubro_id)
            ->update([
                'fact_var_cant'  => $datosValidados['fact_var_cant'],
                'fact_var_monto' => $datosValidados['fact_var_monto'],
                'fact_var_tipo_iva' => $datosValidados['fact_var_tipo_iva']
            ]);

        $actualizado = DB::select("
            SELECT *
            FROM facturas_varias_det
            WHERE factura_varia_id = ? AND rubro_id = ?
        ", [$factura_varia_id, $rubro_id]);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $actualizado
        ], 200);
    }

    public function destroy($factura_varia_id, $rubro_id)
    {
        DB::table('facturas_varias_det')
            ->where('factura_varia_id', $factura_varia_id)
            ->where('rubro_id', $rubro_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }
    
}
