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
            to_char(rvc.remision_vent_fec_ent, 'dd/mm/yyyy HH24:mi:ss') as remision_vent_fec_ent,
            vc.cliente_id,
            per.pers_nombre||' '||per.pers_apellido as nombre_cliente,
            per.pers_ci as cliente_ci,
            cl.cli_ruc,
            per.pers_direc as cli_direc,
            per.pers_telef as cli_telef,
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
            'vehiculo_id'        => 'required',
            'remision_vent_fec'   => 'required',
            'remision_vent_fec_env' => 'nullable',
            'remision_vent_fec_ent' => 'nullable',
            'remision_vent_estado'  => 'required|string'
        ]);

        // El estado siempre lo pone el sistema
        $datosValidados['remision_vent_estado'] = 'PENDIENTE';

        // Verificar que aún no exista remisión para esta venta
        $existe = Remision_vent_cab::where('venta_id', $request->venta_id)->first();
        if ($existe) {
            return response()->json([
               'message' => 'Ya existe una remisión registrada para esta venta.'
            ], 422);
        }

        DB::beginTransaction();

        try {

            $remision = Remision_vent_cab::create($datosValidados);

            $venta_det = DB::table('ventas_det')
            ->where('venta_id', $request->venta_id)
            ->get();

            foreach ($venta_det as $dp) {
                DB::table('remision_vent_det')->insert([
                    'remision_vent_id' => $remision->id,
                    'producto_id'      => $dp->producto_id,
                    'remision_vent_cant'  => $dp->venta_cant,
                    'remision_vent_precio'=> $dp->venta_precio,
                    'remision_vent_obs'   => null
                ]);
            }

            DB::commit();

            return response()->json([
                'mensaje'  => 'Nota de remisión registrada con éxito.',
                'tipo'     => 'success',
                'registro' => $remision
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al registrar',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $remision = Remision_vent_cab::find($id);

        if (!$remision) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'venta_id'           => 'required',
            'cliente_id'         => 'required',
            'empresa_id'         => 'required',
            'sucursal_id'        => 'required',
            'deposito_id'        => 'required',
            'user_id'            => 'required',
            'remision_vent_nro'  => 'required|string|unique:remision_vent_cab,remision_vent_nro,' . $id,
            'remision_motivo_id' => 'required',
            'remision_vent_repartidor' => 'required|string',
            'vehiculo_id'        => 'required',
            'remision_vent_fec'   => 'required',
            'remision_vent_fec_env' => 'nullable',
            'remision_vent_fec_ent' => 'nullable',
            'monto_exentas'         => 'nullable',
            'monto_grav_5'          => 'nullable',
            'monto_grav_10'         => 'nullable',
            'monto_iva_5'           => 'nullable',
            'monto_iva_10'          => 'nullable',
            'monto_general'         => 'nullable',
            'remision_vent_estado'  => 'required|string|in:PENDIENTE,ENVIADO'
        ]);

        if ($remision->remision_vent_estado === 'ENTREGADO' || $remision->remision_vent_estado === 'ANULADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden editar remisiones en estado PENDIENTE o ENVIADO.',
                'tipo' => 'error'
            ], 422);
        }

        $remision->update($datosValidados);

        return response()->json([
            'mensaje' => 'Remisión modificada con éxito',
            'tipo' => 'success',
            'registro' => $remision
        ], 200);
    }

    public function anular(Request $request, $id)
    {
        $remision = Remision_vent_cab::find($id);

        if (!$remision) {
            return response()->json([
                'mensaje' => 'Remisión no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        $remision->remision_vent_estado = 'ANULADO';
        $remision->user_id = $request->user_id; // Registrar quién anuló
        $remision->save();

        return response()->json([
            'mensaje' => 'Remisión anulada con éxito',
            'tipo' => 'success',
            'registro' => $remision
        ], 200);
    }

    public function enviar(Request $r, $id)
    {
        $remision = Remision_vent_cab::find($id);
        if (!$remision) return response()->json(['error'=>'No encontrada'],404);

        if ($remision->remision_vent_estado !== 'PENDIENTE') {
            return response()->json([
                'error' => 'Solo se pueden enviar remisiones en estado PENDIENTE.'
            ], 422);
        }

        $remision->remision_vent_estado = 'ENVIADO';
        $remision->remision_vent_fec_env = $r->remision_vent_fec_env;
        $remision->save();

        return response()->json([
            'mensaje' => 'Remisión marcada como ENVIADO.',
            'tipo'    => 'success',
            'registro' => $remision
        ]);
    }

    public function confirmar(Request $r, $id)
    {
        $remision = Remision_vent_cab::find($id);

        if (!$remision) {
            return response()->json(['error' => 'Remisión no encontrada'], 404);
        }

        // Validar que no esté ya entregada o pendiente
        if ($remision->remision_vent_estado !== 'ENVIADO') {
            return response()->json([
                'message' => 'Solo se pueden confirmar remisiones en estado ENVIADO.'
            ], 422);
        }

        // Validar que venga la fecha de entrega
        if (!$r->filled('remision_vent_fec_ent')) {
            return response()->json(['error' => 'Debe registrar la fecha de entrega para confirmar la remisión'], 422);
        }

        // Obtener detalles
        $detalles = Remision_vent_det::where('remision_vent_id', $id)->get();

        // Inicializar totales
        $monto_grav_5 = 0;
        $monto_grav_10 = 0;
        $monto_iva_5 = 0;
        $monto_iva_10 = 0;
        $monto_exentas = 0;

        foreach ($detalles as $det) {
            // Obtener datos del producto y su impuesto
            $producto = DB::table('productos as p')
                ->join('tipo_impuestos as ti', 'p.impuesto_id', '=', 'ti.id')
                ->where('p.id', $det->producto_id)
                ->select('p.*', 'ti.id as tipo_imp_id', 'ti.impuesto_desc as tipo_imp_desc')
                ->first();

            $subtotal = $det->remision_vent_cant * $det->remision_vent_precio;

            // Calcular IVA según tipo de impuesto
            if ($producto) {
                switch ($producto->tipo_imp_id) {
                    case 2: // 5%
                        $base5 = $subtotal / 1.05;
                        $iva5 = $subtotal - $base5;
                        $monto_grav_5 += $base5;
                        $monto_iva_5 += $iva5;
                        break;
                    case 1: // 10%
                        $base10 = $subtotal / 1.10;
                        $iva10 = $subtotal - $base10;
                        $monto_grav_10 += $base10;
                        $monto_iva_10 += $iva10;
                        break;
                    case 3: // Exento
                    default:
                        $monto_exentas += $subtotal;
                    break;
                }
            }
        }    
        // Actualizar totales y estado
        $remision->remision_vent_fec_ent = $r->remision_vent_fec_ent;
        $remision->remision_vent_estado = 'ENTREGADO';
        $remision->monto_grav_5 = $monto_grav_5;
        $remision->monto_iva_5 = $monto_iva_5;
        $remision->monto_grav_10 = $monto_grav_10;
        $remision->monto_iva_10 = $monto_iva_10;
        $remision->monto_exentas = $monto_exentas;
        $remision->monto_general = $monto_grav_5 + $monto_iva_5 + $monto_grav_10 + $monto_iva_10 + $monto_exentas;
        $remision->save();

        return response()->json([
            'mensaje' => 'Remisión Entregada correctamente.',
            'tipo' => 'success',
            'registro' => $remision
        ], 200);
    }
}
