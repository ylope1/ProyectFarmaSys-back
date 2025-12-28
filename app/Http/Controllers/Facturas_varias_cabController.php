<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Facturas_varias_cab;
use App\Models\Facturas_varias_det;
use App\Models\Ctas_pagar_fact_varias;
use App\Models\Libro_comp_fact_varias;
use App\Models\Proveedore;

class Facturas_varias_cabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT 
                fv.*,
                e.empresa_desc,
                s.suc_desc,
                p.proveedor_desc,
                u.name AS encargado,
                tf.tipo_fact_desc,
                to_char(fv.fact_var_fec, 'dd/mm/yyyy') AS fact_var_fec
            FROM facturas_varias_cab fv
            JOIN proveedores p ON p.id = fv.proveedor_id
            JOIN empresas e ON e.id = fv.empresa_id
            JOIN sucursales s ON s.id = fv.sucursal_id
            JOIN users u ON u.id = fv.user_id
            JOIN tipo_fact tf ON tf.id = fv.tipo_fact_id
        ");
    }

    public function store(Request $r)
    {
        if ($r->fact_var_cant_cta === '') {
            $r->merge(['fact_var_cant_cta' => null]);
        }

        $datos = $r->validate([
            'proveedor_id'      => 'required',
            'user_id'           => 'required',
            'sucursal_id'       => 'required',
            'empresa_id'        => 'required',
            'tipo_fact_id'      => 'required',
            'fact_var_fact'     => 'required|string',
            'fact_var_timbrado' => 'required|integer',
            'fact_var_fec'      => 'required',
            'fact_var_cant_cta' => 'nullable|integer',
            'fact_var_ift'      => 'nullable|integer',
            'monto_exentas'     => 'nullable|numeric',
            'monto_grav_5'      => 'nullable|numeric',
            'monto_grav_10'     => 'nullable|numeric',
            'monto_iva_5'       => 'nullable|numeric',
            'monto_iva_10'      => 'nullable|numeric',
            'monto_general'     => 'nullable|numeric',
            'fact_var_estado'   => 'required|string',
        ]);

        // CONTADO
        if ((int)$r->tipo_fact_id === 6) {
            $datos['fact_var_ift'] = 0;
            $datos['fact_var_cant_cta'] = 0;
        }

        $factura = Facturas_varias_cab::create($datos);

        return response()->json([
            'mensaje'  => 'Factura varias registrada con éxito',
            'tipo'     => 'success',
            'registro' => $factura
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $factura = Facturas_varias_cab::find($id);

        if (!$factura) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($r->fact_var_cant_cta === '') {
            $r->merge(['fact_var_cant_cta' => null]);
        }

        $datos = $r->validate([
            'proveedor_id'      => 'required',
            'user_id'           => 'required',
            'sucursal_id'       => 'required',
            'empresa_id'        => 'required',
            'tipo_fact_id'      => 'required',
            'fact_var_fact'     => 'required|string',
            'fact_var_timbrado' => 'required|integer',
            'fact_var_fec'      => 'required',
            'fact_var_cant_cta' => 'nullable|integer',
            'fact_var_ift'      => 'nullable|integer',
            'monto_exentas'     => 'nullable|numeric',
            'monto_grav_5'      => 'nullable|numeric',
            'monto_grav_10'     => 'nullable|numeric',
            'monto_iva_5'       => 'nullable|numeric',
            'monto_iva_10'      => 'nullable|numeric',
            'monto_general'     => 'nullable|numeric',
            'fact_var_estado'   => 'required|string',
        ]);

        if ((int)$r->tipo_fact_id === 6) {
            $datos['fact_var_ift'] = 0;
            $datos['fact_var_cant_cta'] = 0;
        }

        $factura->update($datos);

        return response()->json([
            'mensaje' => 'Factura modificada con éxito',
            'tipo'    => 'success',
            'registro'=> $factura
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $factura = Facturas_varias_cab::find($id);

        if (!$factura) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $factura->fact_var_estado = 'ANULADO';
        $factura->user_id = $r->user_id;
        $factura->save();

        return response()->json([
            'mensaje' => 'Factura anulada con éxito',
            'tipo'    => 'success',
            'registro'=> $factura
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $factura = Facturas_varias_cab::find($id);

        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        if ($factura->fact_var_estado === 'CONFIRMADO') {
            return response()->json(['error' => 'La factura ya fue confirmada'], 400);
        }

        DB::transaction(function () use ($factura) {

            $detalles = Facturas_varias_det::where('factura_varia_id', $factura->id)->get();

            $monto_exentas = 0;
            $monto_grav_5  = 0;
            $monto_grav_10 = 0;

            foreach ($detalles as $det) {
                $subtotal = $det->fact_var_cant * $det->fact_var_monto;

                switch ($det->fact_var_tipo_iva) {
                    case '5':
                        $monto_grav_5 += $subtotal;
                        break;
                    case '10':
                        $monto_grav_10 += $subtotal;
                        break;
                    case 'EXENTA':
                    default:
                        $monto_exentas += $subtotal;
                    break;
                }
            }
            $monto_iva_5  = round($monto_grav_5 / 21, 2);
            $monto_iva_10 = round($monto_grav_10 / 11, 2);
            $monto_general = $monto_exentas + $monto_grav_5 + $monto_grav_10;
            $factura->update([
                'monto_exentas' => $monto_exentas,
                'monto_grav_5'  => $monto_grav_5,
                'monto_grav_10' => $monto_grav_10,
                'monto_iva_5'   => $monto_iva_5,
                'monto_iva_10'  => $monto_iva_10,
                'monto_general' => $monto_general,
                'fact_var_estado' => 'CONFIRMADO'
            ]);

            // CUENTAS A PAGAR
            if ((int)$factura->tipo_fact_id === 7) { // CRÉDITO
                $cuotas = $factura->fact_var_cant_cta ?? 1;
                $montoCuota = round($monto_general / $cuotas, 2);
                $intervalo = max((int)$factura->fact_var_ift, 1);

                for ($i = 1; $i <= $cuotas; $i++) {
                    Ctas_pagar_fact_varias::create([
                        'factura_varia_id'        => $factura->id,
                        'cta_pagar_fv_monto'      => $montoCuota,
                        'cta_pagar_fv_saldo'      => $montoCuota,
                        'cta_pagar_fv_fec_vto'    => now()->addDays($intervalo * $i),
                        'cta_pagar_fv_nro_cuota'  => $i,
                        'cta_pagar_fv_estado'     => 'PENDIENTE',
                        'tipo_fact_id'            => $factura->tipo_fact_id
                    ]);
                }
            }

            // LIBRO COMPRAS – FACT VARIAS
            $proveedor = Proveedore::find($factura->proveedor_id);

            Libro_comp_fact_varias::create([
                'factura_varia_id'     => $factura->id,
                'lib_comp_fv_fecha'    => $factura->fact_var_fec,
                'proveedor_ruc'        => $proveedor->proveedor_ruc ?? '',
                'lib_comp_fv_tipo_doc' => 'FACTURA',
                'lib_comp_fv_nro_doc'  => $factura->fact_var_fact,
                'lib_comp_fv_monto'    => $factura->monto_general,
                'lib_comp_fv_grav_10'  => $factura->monto_grav_10,
                'lib_comp_fv_iva_10'   => $factura->monto_iva_10,
                'lib_comp_fv_grav_5'   => $factura->monto_grav_5,
                'lib_comp_fv_iva_5'    => $factura->monto_iva_5,
                'lib_comp_fv_exentas'  => $factura->monto_exentas,
                'proveedor_id'         => $proveedor->id,
                'proveedor_desc'       => $proveedor->proveedor_desc ?? '',
                'impuesto_id'          => 8,
                'impuesto_desc'        => 'Mixto'
            ]);
        });

        return response()->json([
            'mensaje' => 'Factura confirmada. Cuentas a pagar y libro de compras generados.',
            'tipo'    => 'success',
            'registro'=> $factura
        ], 200);
    }

    
}
