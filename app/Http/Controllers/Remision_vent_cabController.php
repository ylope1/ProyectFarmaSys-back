<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Remision_vent_cab;
use App\Models\Remision_vent_det;
use App\Models\Ventas_cab;
use App\Models\Ventas_det;
use Illuminate\Support\Facades\DB;

class Remision_vent_cabController extends Controller
{
    public function read()
    {
        return DB::select("
        SELECT 
            rvc.*,
            e.empresa_desc,
            s.suc_desc,
            d.deposito_desc,
            v.vehiculo_desc,
            m.remision_motivo_desc,
            to_char(rvc.remision_vent_fec, 'dd/mm/yyyy HH24:mi:ss') as remision_vent_fec,
            to_char(rvc.remision_vent_fec_env, 'dd/mm/yyyy HH24:mi:ss') as remision_vent_fec_env,
            to_char(rc.remision_vent_fec_ent, 'dd/mm/yyyy HH24:mi:ss') as remision_vent_fec_ent,
            vc.cliente_id,
            per.pers_nombre||' '||per.pers_apellido as nombre_cliente,
            per.pers_ci as cliente_ci,
            cl.cli_ruc,
            per.pers_direc as cliente_direccion,
            per.pers_telef as cliente_telefono,
            u.name as vendedor,
            COALESCE(
                'VENTA NRO: ' || LPAD(vc.id::text, 7, '0') ||
                ' - FECHA: ' || to_char(vc.venta_fec, 'YYYY-MM-DD HH24:MI:SS') ||
                ' - ESTADO: ' || vc.venta_estado,
                'SIN VENTA ASOCIADA'
            ) as venta
            FROM remision_vent_cab rvc
            JOIN clientes cl ON rvc.cliente_id = cl.id
            JOIN personas per ON cl.persona_id = per.id
            JOIN empresas e ON e.id = rvc.empresa_id
            JOIN sucursales s ON s.id = rvc.sucursal_id
            JOIN depositos d ON d.id = rvc.deposito_id
            JOIN users u ON u.id = rvc.user_id
            JOIN vehiculos v ON v.id = rvc.vehiculo_id
            JOIN remision_motivo m ON m.id = rvc.remision_motivo_id
            LEFT JOIN ventas_cab vc ON vc.id = rvc.venta_id;
        ");
    }

    public function store(Request $request)
    {
        // Convertir campo de llegada vacío a null
        if ($request->remision_vent_fec_ent === '') {
            $request->merge(['remision_vent_fec_ent' => null]);
        }

        $datosValidados = $request->validate([
            'venta_id'           => 'required',
            'cliente_id'         => 'required',
            'empresa_id'         => 'required',
            'sucursal_id'        => 'required',
            'deposito_id'        => 'required',
            'user_id'            => 'required',
            'remision_vent_nro'  => 'required|string|unique:remision_vent_cab,remision_vent_nro',
            'remision_motivo_id' => 'required',
            'remision_vent_repartidor' => 'required|string',
            'vehiculo_id',        => 'required',
            'remision_vent_fec'   => 'required',
            'remision_vent_fec_env' => 'required',
            'remision_vent_fec_ent' => 'nullable',
            'remision_vent_estado'  => 'required|string|in:PENDIENTE,ENVIADO,ENTREGADO,ANULADO'
        ]);

        // Verificar que aún no exista remisión para esta venta
        $existe = Remision_vent_cab::where('venta_id', $request->venta_id)->first();
        if ($existe) {
            return response()->json([
               'message' => 'Ya existe una remisión registrada para esta venta.'
            ], 422);
        }

        $remision = Remision_vent_cab::create($datosValidados);

            $venta_det = DB::select("
                SELECT 
                vd.producto_id,
                vd.venta_cant,
                vd.venta_precio
                FROM ventas_det vd
                JOIN productos p ON p.id = vd.producto_id
                WHERE vd.venta_id = ?
                ", [$request->venta_id]
            );

            foreach ($ventas_det as $dp) {
                DB::table('remision_vent_det')->insert([
                    'remision_vent_id' => $remision->id,
                    'producto_id'      => $dp->producto_id,
                    'remision_vent_cant'    => $dp->venta_cant,
                    'remision_vent_precio'   => $dp->venta_precio,
                    'remision_vent_obs'     => null //se completa desde el front si hace falta
                ]);
            }
           

        return response()->json([
            'mensaje'  => 'Nota de remisión registrada con éxito.',
            'tipo'     => 'success',
            'registro' => $remision
        ], 200);
    }
    //cargar aca las demas funciones

    
}
