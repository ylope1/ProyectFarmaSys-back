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
        $venta = Ventas_cab::find($id);

        if (!$venta) {
            return response()->json(['error' => 'Venta no encontrada'], 404);
        }

        // Validar que no esté ya confirmada
        if ($venta->venta_estado === 'CONFIRMADO') {
            return response()->json(['error' => 'Esta venta ya fue confirmada anteriormente'], 400);
        }

        // Marcar venta como CONFIRMADO
        $venta->venta_estado = 'CONFIRMADO';
        $venta->save();

        // Obtener detalles de ventas
        $detalles = Ventas_det::where('venta_id', $id)->get();

        // Inicializar acumuladores de totales
        $monto_grav_5 = 0;
        $monto_grav_10 = 0;
        $monto_iva_5 = 0;
        $monto_iva_10 = 0;
        $monto_exentas = 0;

        foreach ($detalles as $det) {
        $producto = DB::table('productos as p')
        ->join('tipo_impuestos as ti', 'p.impuesto_id', '=', 'ti.id')
        ->where('p.id', $det->producto_id)
        ->select('p.*', 'ti.id as tipo_imp_id', 'ti.impuesto_desc as tipo_imp_desc')
        ->first();

        $subtotal = $det->compra_cant * $det->compra_costo;

        if ($producto) {
            switch ($producto->tipo_imp_id) {
                case 2: // 5% IVA
                    $base5 = $subtotal / 1.05;
                    $iva5 = $subtotal - $base5;
                    $monto_grav_5 += $base5;
                    $monto_iva_5 += $iva5;
                    break;
                case 1: // 10% IVA
                    $base10 = $subtotal / 1.10;
                    $iva10 = $subtotal - $base10;
                    $monto_grav_10 += $base10;
                    $monto_iva_10 += $iva10;
                    break;
                case 3: // Exentas
                default:
                    $monto_exentas += $subtotal;
                    break;
            }
        }

            // Buscar stock existente
            $stock = Stock::where('deposito_id', $venta->deposito_id)
                        ->where('sucursal_id', $venta->sucursal_id)
                        ->where('producto_id', $producto->id)
                        ->first();

            if ($stock) {
                $nuevoTotal = $stock->stock_cant_exist - $det->venta_cant;

                if ($nuevoTotal < $stock->stock_cant_min) {
                    $faltante = $nuevoTotal + $stock->stock_cant_min;

                    $stock->stock_cant_exist = $stock->stock_cant_min;
                    $stock->cantidad_exceso += $faltante;
                    $stock->fecha_movimiento =  $venta->venta_fec; 
                    $stock->motivo = 'STOCK MINIMO DE PRODUCTO PARA VENTA';
                    $stock->save();

                } else {
                    $stock->stock_cant_exist = $nuevoTotal;
                    $stock->fecha_movimiento =  $venta->venta_fec;
                    $stock->motivo = 'SALIDA VENTA';
                    $stock->save();
                }
            } else {
                // Crear nuevo stock
                Stock::create([
                    'deposito_id' => $venta->deposito_id,
                    'sucursal_id' => $venta->sucursal_id,
                    'producto_id' => $producto->id,
                    'stock_cant_exist' => $det->venta_cant,
                    'stock_cant_min' => 0,
                    'stock_cant_max' => 100,
                    'cantidad_exceso' => 0,
                    'fecha_movimiento' =>  $venta->venta_fec,
                    'motivo' => 'SALIDA VENTA'
                ]);
            }
        }

            // Guardar totales en cabecera
            $venta->monto_grav_5 = $monto_grav_5;
            $venta->monto_iva_5 = $monto_iva_5;
            $venta->monto_grav_10 = $monto_grav_10;
            $venta->monto_iva_10 = $monto_iva_10;
            $venta->monto_exentas = $monto_exentas;
            $venta->monto_general = $monto_grav_5 + $monto_iva_5 + $monto_grav_10 + $monto_iva_10 + $monto_exentas;
            $venta->save();

            // Si es crédito, generar cuentas a cobrar
            if ((int) $venta->tipo_fact_id === 7) { // 7 = crédito
                $cuotas = $venta->venta_cant_cta ?? 1;
                $montoPorCuota = $venta->monto_general / $cuotas;
                $intervalo = $venta->venta_ifv ?? 30;

                for ($i = 1; $i <= $cuotas; $i++) {
                    Ctas_cobrar::create([
                        'id' => $i,
                        'venta_id' => $venta->id,
                        'ctas_cob_monto' => $montoPorCuota,
                        'ctas_cob_saldo' => $montoPorCuota, 
                        'ctas_cob_fec_vto' => now()->addDays($intervalo * $i),
                        'ctas_cob_nro_cuota' => $i,
                        'ctas_cob_estado' => 'Pendiente',
                        'tipo_fact_id' => $venta->tipo_fact_id
                    ]);
                }
            }

            // Registrar en Libro de Ventas
            $primerDetalle = $detalles->first();
            $producto = DB::table('productos as p')
                ->join('tipo_impuestos as ti', 'p.impuesto_id', '=', 'ti.id')
                ->where('p.id', $primerDetalle->producto_id)
                ->select('p.*', 'ti.id as tipo_imp_id', 'ti.impuesto_desc as tipo_imp_desc')
                ->first();
            $clientes = Clientes::find($venta->cliente_id);

            Libro_Ventas::create([
            'venta_id' => $venta->id,
            'lib_vent_fecha' => $venta->venta_fec,
            'cli_ruc' => $clientes->cli_ruc ?? '', 
            'lib_vent_tipo_doc' => 'FACTURA',
            'lib_vent_nro_doc' => $venta->venta_fact,
            'lib_vent_monto' => $venta->monto_general,
            'lib_vent_grav_10' => $monto_grav_10,
            'lib_vent_iva_10' => $monto_iva_10,
            'lib_vent_grav_5' => $monto_grav_5,
            'lib_vent_iva_5' => $monto_iva_5,
            'lib_vent_exentas' => $monto_exentas,
            'cliente_id' => $clientes->id,
            'cliente_nombre' => $clientes->nombre_cliente ?? '',
            'impuesto_id' => $producto->tipo_imp_id ?? null, 
            'impuesto_desc' => $producto->tipo_imp_desc ?? '', 
        ]);

        return response()->json([
            'mensaje' => 'Venta confirmada, stock actualizado, libro ventas y cuenta a cobrar generados correctamente.',
            'tipo' => 'success',
            'registro' => $venta
        ], 200);
    }
}
