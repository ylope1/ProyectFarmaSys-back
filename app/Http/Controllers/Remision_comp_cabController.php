<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Remision_comp_cabController;

class Remision_comp_cabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            rc.*,
            e.empresa_desc,
            so.suc_desc AS sucursal_origen,
            sd.suc_desc AS sucursal_destino,
            di.deposito_desc AS deposito_origen,
            dd.deposito_desc AS deposito_destino,
            v.vehiculo_desc,
            m.remision_motivo_desc,
            to_char(rc.rem_comp_fec, 'dd/mm/yyyy HH24:mi:ss') as rem_comp_fec,
            to_char(rc.rem_comp_fec_sal, 'dd/mm/yyyy HH24:mi:ss') as rem_comp_fec_sal,
            to_char(rc.rem_comp_fec_recep, 'dd/mm/yyyy HH24:mi:ss') as rem_comp_fec_recep,
            u.name as encargado,
            COALESCE(
                'PEDIDO NRO: ' || LPAD(p.id::text, 7, '0') ||
                ' - FECHA: ' || to_char(p.pedido_comp_fec, 'YYYY-MM-DD HH24:MI:SS') ||
                ' - ESTADO: ' || p.pedido_comp_estado,
                'SIN PEDIDO ASOCIADO'
            ) as pedido
            FROM remision_comp_cab rc
            JOIN empresas e ON e.id = rc.empresa_id
            JOIN sucursales so ON so.id = rc.sucursal_origen_id
            JOIN sucursales sd ON sd.id = rc.sucursal_destino_id
            JOIN depositos di ON di.id = rc.deposito_origen_id
            JOIN depositos dd ON dd.id = rc.deposito_destino_id
            JOIN users u ON u.id = rc.user_id
            JOIN vehiculos v ON v.id = rc.vehiculo_id
            JOIN remision_motivo m ON m.id = rc.remision_motivo_id
            LEFT JOIN pedidos_comp_cab p ON p.id = rc.pedido_comp_id;
        ");
    }
    public function store(Request $request)
    {
        // Convertir campo de llegada vacío a null
        if ($request->rem_comp_fec_recep === '') {
            $request->merge(['rem_comp_fec_recep' => null]);
        }

        $datosValidados = $request->validate([
            'pedido_comp_id'        => 'nullable',
            'user_id'               => 'required',
            'sucursal_origen_id'    => 'required',
            'sucursal_destino_id'   => 'required',
            'deposito_origen_id'    => 'required',
            'deposito_destino_id'   => 'required',
            'empresa_id'            => 'required',
            'rem_comp_nro'          => 'required|unique',
            'remision_motivo_id'    => 'required',
            'rem_comp_fec'          => 'required',
            'rem_comp_fec_sal'      => 'required',
            'rem_comp_fec_recep'    => 'nullable',
            'vehiculo_id'           => 'required',
            'chofer'                => 'required',
            'rem_comp_estado'       => 'required|string|in:PENDIENTE,CONFIRMADO,ANULADO'
        ]);

        // Convertir pedido_comp_id = 0 a null
        if ((int) $request->pedido_comp_id === 0) {
            $datosValidados['pedido_comp_id'] = null;
        }

        $remision = Remision_comp_cab::create($datosValidados);

        // Si hay un pedido asociado, lo marcamos como PROCESADO y copiamos los detalles
        if ($request->filled('pedido_comp_id')) {
            DB::table('pedidos_comp_cab')
                ->where('id', $request->pedido_comp_id)
                ->update(['pedido_comp_estado' => 'PROCESADO']);

            $detalles = DB::table('pedidos_comp_det')
                ->where('pedido_comp_id', $request->pedido_comp_id)
                ->get();

            foreach ($detalles as $det) {
                DB::table('remision_comp_det')->insert([
                    'remision_comp_id' => $remision->id,
                    'producto_id'      => $det->producto_id,
                    'rem_comp_cant'    => $det->pedido_comp_cant,
                    'rem_comp_costo'   => $det->pedido_comp_costo,
                    'rem_comp_obs'     => $det->pedido_comp_obs ?? null
                ]);
            }
        }

        return response()->json([
            'mensaje'  => 'Nota de remisión registrada con éxito.',
            'tipo'     => 'success',
            'registro' => $remision
        ], 200);
    }    
}
