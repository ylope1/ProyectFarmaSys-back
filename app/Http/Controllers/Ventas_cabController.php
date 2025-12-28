<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ventas_cab;
use App\Models\Ventas_det;
use App\Models\Pedidos_vent_cab;
use App\Models\Pedidos_vent_det;
use App\Models\Producto;
use App\Models\Tipo_impuesto;
use App\Models\Stock;
use App\Models\Deposito;
use App\Models\Ctas_cobrar;
use App\Models\Libro_Ventas;
use App\Models\Clientes;

class Ventas_cabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT 
                vc.*,
                e.empresa_desc,
                s.suc_desc,
                d.deposito_desc,
                to_char(vc.venta_fec, 'dd/mm/yyyy') as venta_fec,
                vc.cliente_id,
                per.pers_nombre||' '||per.pers_apellido as nombre_cliente,
                per.pers_ci as cliente_ci,
                cl.cli_ruc,
                u.name as vendedor,
                tf.tipo_fact_desc,
                COALESCE(
                'PEDIDO NRO: ' || LPAD(pvc.id::text, 7, '0') ||
                ' - FECHA CONF: ' || to_char(pvc.pedido_vent_fec_conf, 'YYYY-MM-DD HH24:MI:SS') ||
                ' - ESTADO PEDIDO: ' || pvc.pedido_vent_estado,
                'SIN PEDIDO DE COMPRA'
                ) as pedido
                FROM ventas_cab vc
                JOIN clientes cl ON vc.cliente_id = cl.id
                JOIN personas per ON cl.persona_id = per.id
                JOIN empresas e ON e.id = vc.empresa_id
                JOIN sucursales s ON s.id = vc.sucursal_id
                JOIN depositos d on d.id = vc.deposito_id
                JOIN users u ON u.id = vc.user_id
                JOIN tipo_fact tf ON tf.id = vc.tipo_fact_id
                LEFT JOIN pedidos_vent_cab pvc ON pvc.id = vc.pedido_vent_id
            ");
    }

    public function store(Request $request)
    {
        // Si venta_cant_cta viene vacío, lo convertimos a null
        if ($request->venta_cant_cta === '') {
        $request->merge(['venta_cant_cta' => null]);
        }

        $datosValidados = $request->validate([
            'pedido_vent_id'         => 'nullable',
            'cliente_id'          => 'required',
            'user_id'               => 'required',
            'sucursal_id'           => 'required',
            'deposito_id'          => 'required',
            'empresa_id'            => 'required',
            'tipo_fact_id'          => 'required',
            'venta_fact'           => 'required',
            'venta_timbrado'       => 'required',
            'venta_fec'            => 'required',
            'venta_cant_cta'       => 'nullable',
            'venta_ifv'            => 'nullable',
            'monto_exentas'         => 'nullable',
            'monto_grav_5'          => 'nullable',
            'monto_grav_10'         => 'nullable',
            'monto_iva_5'           => 'nullable',
            'monto_iva_10'          => 'nullable',
            'monto_general'         => 'nullable',
            'venta_estado'         => 'required',
        ]);
                // Corregir pedido_vent_id = 0 a null
        if ((int) $request->pedido_vent_id === 0) {
            $datosValidados['pedido_vent_id'] = null;
}
        // Verificamos si el tipo de factura es contado (por ejemplo, ID = 1)
        $tipoContadoId = 6;

        if ((int) $request->tipo_fact_id === $tipoContadoId) {
            $datosValidados['venta_ifv'] = 0; // compras al contado tienen 0 dias de vto
            $datosValidados['venta_cant_cta'] = 0; // compras al contado no tienen cuotas
        }

        $venta = Ventas_cab::create($datosValidados);

        // Si tiene pedido de venta asociada
        if ($request->filled('pedido_vent_id')) {
            $pedido = Pedidos_vent_cab::find($request->pedido_vent_id);
            if ($pedido) {
                $pedido->pedido_vent_estado = "ENVIADO";
                $pedido->pedido_vent_fec_env = $venta->venta_fec;
                $pedido->save();
                

                // Copiar detalles de pedidos a ventas
                $detalles = DB::table('pedidos_vent_det')
                    ->where('pedido_vent_id', $pedido->id)
                    ->get();

                foreach ($detalles as $det) {
                    Ventas_det::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $det->producto_id,
                        'venta_cant' => $det->pedido_vent_cant,
                        'venta_precio' => $det->pedido_vent_precio
                    ]);
                }
            }
        }

        return response()->json([
            'mensaje'  => 'Venta registrada con éxito',
            'tipo'     => 'success',
            'registro' => $venta
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $venta = Ventas_cab::find($id);
        if (!$venta) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

         // Convertimos campos vacíos a null
        if ($request->venta_cant_cta === '') {
            $request->merge(['venta_cant_cta' => null]);
        }

        $datosValidados = $request->validate([
            'pedido_vent_id'         => 'nullable',
            'cliente_id'          => 'required',
            'user_id'               => 'required',
            'sucursal_id'           => 'required',
            'deposito_id'          => 'required',
            'empresa_id'            => 'required',
            'tipo_fact_id'          => 'required',
            'venta_fact'           => 'required',
            'venta_timbrado'       => 'required',
            'venta_fec'            => 'required',
            'venta_cant_cta'       => 'nullable',
            'venta_ifv'            => 'nullable',
            'monto_exentas'         => 'nullable',
            'monto_grav_5'          => 'nullable',
            'monto_grav_10'         => 'nullable',
            'monto_iva_5'           => 'nullable',
            'monto_iva_10'          => 'nullable',
            'monto_general'         => 'nullable',
            'venta_estado'         => 'required',
        ]);
        
         $tipoContadoId = 6;
        if ((int) $request->tipo_fact_id === $tipoContadoId) {
            $datosValidados['venta_ifv'] = 0;
            $datosValidados['venta_cant_cta'] = 0;
        }

        $venta->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $venta
        ], 200);
    }

    public function destroy($id)
    {
        $venta = Ventas_cab::find($id);
        if (!$venta) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $venta->delete();
        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    public function anular(Request $request, $id)
    {
        $venta = Ventas_cab::find($id);
        if (!$venta) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $venta->venta_estado = 'ANULADO';
        $venta->user_id = $request->user_id; // Registrar quién anuló
        $venta->save();

        // Si está asociada a un pedido, restaurar su estado a CONFIRMADO
        if ($venta->pedido_vent_id) {
            $pedido = Pedidos_ven_cab::find($venta->pedido_vent_id);
            if ($pedido) {
                $pedido->pedido_vent_estado = 'CONFIRMADO';
                $pedido->save();
            }
        }

        return response()->json([
            'mensaje'  => 'Venta anulada con éxito',
            'tipo'     => 'success',
            'registro' => $venta
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        DB::beginTransaction();

        try {

            $venta = Ventas_cab::find($id);

            if (!$venta) {
                return response()->json(['error' => 'Venta no encontrada'], 404);
            }

            if ($venta->venta_estado === 'CONFIRMADO') {
                return response()->json(['error' => 'Esta venta ya fue confirmada anteriormente'], 400);
            }

            //CONFIRMAR VENTA
            $venta->venta_estado = 'CONFIRMADO';
            $venta->save();

            $detalles = Ventas_det::where('venta_id', $id)->get();

            $monto_grav_5  = 0;
            $monto_grav_10 = 0;
            $monto_iva_5   = 0;
            $monto_iva_10  = 0;
            $monto_exentas = 0;

            foreach ($detalles as $det) {

                $producto = DB::table('productos as p')
                    ->join('tipo_impuestos as ti', 'p.impuesto_id', '=', 'ti.id')
                    ->where('p.id', $det->producto_id)
                    ->select('p.*', 'ti.id as tipo_imp_id')
                    ->first();

                $subtotal = $det->venta_cant * $det->venta_precio;

                switch ($producto->tipo_imp_id) {
                    case 2: // IVA 5%
                        $base5 = $subtotal / 1.05;
                        $monto_grav_5 += $base5;
                        $monto_iva_5  += ($subtotal - $base5);
                        break;

                    case 1: // IVA 10%
                        $base10 = $subtotal / 1.10;
                        $monto_grav_10 += $base10;
                        $monto_iva_10  += ($subtotal - $base10);
                        break;

                    default: // Exentas
                        $monto_exentas += $subtotal;
                        break;
                }

                //ACTUALIZAR STOCK
                $stock = Stock::where('deposito_id', $venta->deposito_id)
                    ->where('sucursal_id', $venta->sucursal_id)
                    ->where('producto_id', $producto->id)
                    ->first();

                if ($stock) {
                    $stock->stock_cant_exist -= $det->venta_cant;
                    $stock->fecha_movimiento = $venta->venta_fec;
                    $stock->motivo = 'SALIDA VENTA';
                    $stock->save();
                }
            }

            //TOTALES EN CABECERA
            $venta->monto_grav_5  = $monto_grav_5;
            $venta->monto_iva_5   = $monto_iva_5;
            $venta->monto_grav_10 = $monto_grav_10;
            $venta->monto_iva_10  = $monto_iva_10;
            $venta->monto_exentas = $monto_exentas;
            $venta->monto_general = $monto_grav_5 + $monto_iva_5 +$monto_grav_10 + $monto_iva_10 +$monto_exentas;

            $venta->save();

            //GENERAR CUENTAS A COBRAR

            if (Ctas_cobrar::where('venta_id', $venta->id)->exists()) {
                throw new \Exception('La venta ya tiene cuentas a cobrar generadas');
            }

            // CONTADO (tipo_fact_id = 6)
            if ((int) $venta->tipo_fact_id === 6) {
                $idCta = $this->siguienteIdCtaCobrar($venta->id);
                Ctas_cobrar::create([
                    'id'                 => $idCta,
                    'venta_id'           => $venta->id,
                    'ctas_cob_monto'     => $venta->monto_general,
                    'ctas_cob_saldo'     => $venta->monto_general,
                    'ctas_cob_fec_vto'   => $venta->venta_fec, 
                    'ctas_cob_nro_cuota' => 1,
                    'ctas_cob_estado'    => 'PENDIENTE',
                    'tipo_fact_id'       => $venta->tipo_fact_id
                ]);
            }

            // CRÉDITO (tipo_fact_id = 7)
            if ((int) $venta->tipo_fact_id === 7) {

                $cuotas     = max(1, (int) $venta->venta_cant_cta);
                $intervalo  = max(1, (int) $venta->venta_ifv);
                $montoCuota = round($venta->monto_general / $cuotas, 2);

                for ($i = 1; $i <= $cuotas; $i++) {

                    $idCta = $this->siguienteIdCtaCobrar($venta->id);

                    Ctas_cobrar::create([
                        'id'                 => $idCta,
                        'venta_id'           => $venta->id,
                        'ctas_cob_monto'     => $montoCuota,
                        'ctas_cob_saldo'     => $montoCuota,
                        'ctas_cob_fec_vto'   => now()->addDays($intervalo * $i),
                        'ctas_cob_nro_cuota' => $i,
                        'ctas_cob_estado'    => 'PENDIENTE',
                        'tipo_fact_id'       => $venta->tipo_fact_id
                    ]);
                }
            }

            //LIBRO DE VENTAS
            $primerDetalle = $detalles->first();
            $producto = DB::table('productos as p')
                ->join('tipo_impuestos as ti', 'p.impuesto_id', '=', 'ti.id')
                ->where('p.id', $primerDetalle->producto_id)
                ->select('p.*', 'ti.id as tipo_imp_id', 'ti.impuesto_desc as tipo_imp_desc')
                ->first();
            $cliente = Clientes::find($venta->cliente_id);

            Libro_Ventas::create([
                'venta_id'          => $venta->id,
                'lib_vent_fecha'    => $venta->venta_fec,
                'cli_ruc'           => $cliente->cli_ruc ?? '',
                'lib_vent_tipo_doc' => 'FACTURA',
                'lib_vent_nro_doc'  => $venta->venta_fact,
                'lib_vent_monto'    => $venta->monto_general,
                'lib_vent_grav_10'  => $venta->monto_grav_10,
                'lib_vent_iva_10'   => $venta->monto_iva_10,
                'lib_vent_grav_5'   => $venta->monto_grav_5,
                'lib_vent_iva_5'    => $venta->monto_iva_5,
                'lib_vent_exentas'  => $venta->monto_exentas,
                'cliente_id'        => $cliente->id,
                'cliente_nombre'    => $cliente->nombre_cliente ?? '',
                'impuesto_id' => $producto->tipo_imp_id ?? null, 
                'impuesto_desc' => $producto->tipo_imp_desc ?? '',
            ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Venta confirmada y cuentas a cobrar generadas correctamente',
                'tipo'    => 'success',
                'registro'=> $venta
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al confirmar venta',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    private function siguienteIdCtaCobrar($ventaId)
    {
        return (int) DB::table('ctas_cobrar')
            ->where('venta_id', $ventaId)
            ->max('id') + 1;
    }

    public function buscar(Request $r)
    {
        return DB::select("SELECT 
            vc.id,
            to_char(vc.venta_fec, 'dd/mm/yyyy HH24:mi:ss') AS venta_fec,
            vc.venta_estado,
            vc.empresa_id,  
            e.empresa_desc,
            vc.sucursal_id, 
            s.suc_desc,
            vc.user_id, 
            u.name AS vendedor,
            vc.id as venta_id,
            'VENTA NRO:' || to_char(vc.id, '0000000') || 
            ' FECHA: ' || to_char(vc.venta_fec, 'dd/mm/yyyy HH24:mi:ss') || 
            ' (' || vc.venta_estado || ')' AS venta
        FROM ventas_cab vc 
        JOIN empresas e ON e.id = vc.empresa_id
        JOIN sucursales s ON s.id = vc.sucursal_id 
        JOIN users u ON u.id = vc.user_id 
        WHERE vc.venta_estado = 'CONFIRMADO' 
        AND vc.user_id = ? 
        AND u.name ILIKE ?
        ", [$r->user_id, '%' . $r->name . '%']);
    }

    public function buscarVentFactSuc(Request $r)
    {
        $query = "SELECT 
            vc.id,
            to_char(vc.venta_fec, 'dd/mm/yyyy HH24:mi:ss') AS venta_fec,
            vc.venta_estado,
            vc.venta_fact,  -- Número de factura
            vc.empresa_id,  
            e.empresa_desc,
            vc.sucursal_id, 
            s.suc_desc,
            vc.user_id, 
            u.name AS vendedor,
            vc.id as venta_id,
            'VENTA NRO:' || to_char(vc.id, '0000000') || 
            ' FECHA: ' || to_char(vc.venta_fec, 'dd/mm/yyyy HH24:mi:ss') || 
            ' (' || vc.venta_estado || ')' AS venta,
            'FACTURA: ' || vc.venta_fact AS venta_fact
        FROM ventas_cab vc 
        JOIN empresas e ON e.id = vc.empresa_id
        JOIN sucursales s ON s.id = vc.sucursal_id 
        JOIN users u ON u.id = vc.user_id 
        WHERE vc.venta_estado = 'CONFIRMADO'";
        
        $params = [];
        
        // FILTRO OBLIGATORIO por sucursal (seguridad multi-sucursal)
        if ($r->has('sucursal_id') && !empty($r->sucursal_id)) {
            $query .= " AND vc.sucursal_id = ?";
            $params[] = $r->sucursal_id;
        } else {
            // Opción 1: Devolver error
            return response()->json([
                'error' => true,
                'mensaje' => 'Se requiere el parámetro sucursal_id'
            ], 400);
        }
        
        // Filtro opcional por número de factura
        if ($r->has('venta_fact') && !empty($r->venta_fact)) {
            $query .= " AND vc.venta_fact ILIKE ?";
            $params[] = '%' . $r->venta_fact . '%';
        }
        // Ordenar por fecha descendente (más recientes primero)
        $query .= " ORDER BY vc.venta_fec DESC";
        return DB::select($query, $params);
    }
}
