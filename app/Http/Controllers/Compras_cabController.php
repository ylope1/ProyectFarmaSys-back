<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compras_cab;
use App\Models\Compras_det;
use App\Models\Orden_comp_cab;
use App\Models\Orden_comp_det;
use App\Models\Producto;
use App\Models\Tipo_impuesto;
use App\Models\Stock;
use App\Models\Deposito_producto;
use App\Models\Ctas_pagar;
use App\Models\Libro_compras;
use App\Models\Proveedor;
use App\Models\Proveedore;
use Illuminate\Support\Facades\DB;

class Compras_cabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT 
                cc.*,
                to_char(cc.compra_fec, 'dd/mm/yyyy') as compra_fec,
                to_char(cc.compra_fec_recep, 'dd/mm/yyyy') as compra_fec_recep,
                p.proveedor_desc,
                e.empresa_desc,
                s.suc_desc,
                u.name as encargado,
                tf.tipo_fact_desc,
                COALESCE(
                'ORDEN NRO: ' || LPAD(oc.id::text, 7, '0') ||
                ' - IFV: ' || oc.orden_comp_ifv ||
                ' - FECHA APROB: ' || to_char(oc.orden_comp_fec_aprob, 'YYYY-MM-DD HH24:MI:SS') ||
                ' - ESTADO ORDEN: ' || oc.orden_comp_estado,
                'SIN ORDEN DE COMPRA'
                ) as orden
                FROM compras_cab cc
                JOIN proveedores p ON p.id = cc.proveedor_id
                JOIN empresas e ON e.id = cc.empresa_id
                JOIN sucursales s ON s.id = cc.sucursal_id
                JOIN users u ON u.id = cc.user_id
                JOIN tipo_fact tf ON tf.id = cc.tipo_fact_id
                LEFT JOIN orden_comp_cab oc ON oc.id = cc.orden_comp_id
            ");
    }

    public function store(Request $request)
    {
        // Si compra_cant_cta viene vacío, lo convertimos a null
        if ($request->compra_cant_cta === '') {
        $request->merge(['compra_cant_cta' => null]);
        }

        $datosValidados = $request->validate([
            'orden_comp_id'         => 'nullable',
            'proveedor_id'          => 'required',
            'user_id'               => 'required',
            'sucursal_id'           => 'required',
            'empresa_id'            => 'required',
            'tipo_fact_id'          => 'required',
            'compra_fact'           => 'required|string',
            'compra_timbrado'       => 'required|integer',
            'compra_fec'            => 'required|date',
            'compra_fec_recep'      => 'required|date',
            'compra_cant_cta'       => 'nullable|integer',
            'compra_ifv'            => 'nullable|integer',
            'compra_estado'         => 'required|string',
            'total_exentas'         => 'nullable|numeric',
            'total_grav_5'          => 'nullable|numeric',
            'total_grav_10'         => 'nullable|numeric',
            'total_iva_5'           => 'nullable|numeric',
            'total_iva_10'          => 'nullable|numeric',
            'total_general'         => 'nullable|numeric',
        ]);
        // Verificamos si el tipo de factura es contado (por ejemplo, ID = 1)
        $tipoContadoId = 6;

        if ((int) $request->tipo_fact_id === $tipoContadoId) {
            $datosValidados['compra_ifv'] = 0; // compras al contado tienen 0 dias de vto
            $datosValidados['compra_cant_cta'] = 0; // compras al contado no tienen cuotas
        }

        $compra = Compras_cab::create($datosValidados);

        // Si tiene orden de compra asociada
        if ($request->filled('orden_comp_id')) {
            $orden = Orden_comp_cab::find($request->orden_comp_id);
            if ($orden) {
                $orden->orden_comp_estado = "PROCESADO";
                $orden->save();

                // Copiar detalles de orden a compra
                $detalles = DB::table('orden_comp_det')
                    ->where('orden_comp_id', $orden->id)
                    ->get();

                foreach ($detalles as $det) {
                    Compras_det::create([
                        'compra_id' => $compra->id,
                        'producto_id' => $det->producto_id,
                        'compra_cant' => $det->orden_comp_cant,
                        'compra_costo' => $det->orden_comp_costo
                    ]);
                }
            }
        }

        return response()->json([
            'mensaje'  => 'Compra registrada con éxito',
            'tipo'     => 'success',
            'registro' => $compra
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $compra = Compras_cab::find($id);
        if (!$compra) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

         // Convertimos campos vacíos a null
        if ($request->compra_cant_cta === '') {
            $request->merge(['compra_cant_cta' => null]);
        }

        $datosValidados = $request->validate([
            'orden_comp_id'         => 'nullable',
            'proveedor_id'          => 'required',
            'user_id'               => 'required',
            'sucursal_id'           => 'required',
            'empresa_id'            => 'required',
            'tipo_fact_id'          => 'required',
            'compra_fact'           => 'required|string',
            'compra_timbrado'       => 'required|integer',
            'compra_fec'            => 'required|date',
            'compra_fec_recep'      => 'required|date',
            'compra_cant_cta'       => 'nullable|integer',
            'compra_ifv'            => 'nullable|integer',
            'compra_estado'         => 'required|string',
            'total_exentas'         => 'nullable|numeric',
            'total_grav_5'          => 'nullable|numeric',
            'total_grav_10'         => 'nullable|numeric',
            'total_iva_5'           => 'nullable|numeric',
            'total_iva_10'          => 'nullable|numeric',
            'total_general'         => 'nullable|numeric',
        ]);
        
         $tipoContadoId = 6;
        if ((int) $request->tipo_fact_id === $tipoContadoId) {
            $datosValidados['compra_ifv'] = 0;
            $datosValidados['compra_cant_cta'] = 0;
        }

        $compra->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $compra
        ], 200);
    }

    public function destroy($id)
    {
        $compra = Compras_cab::find($id);
        if (!$compra) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $compra->delete();
        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    public function anular(Request $request, $id)
    {
        $compra = Compras_cab::find($id);
        if (!$compra) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $compra->compra_estado = 'ANULADO';
        $compra->user_id = $request->user_id; // Registrar quién anuló
        $compra->save();

        // Si está asociada a una orden, restaurar su estado a APROBADO
        if ($compra->orden_comp_id) {
            $orden = Orden_comp_cab::find($compra->orden_comp_id);
            if ($orden) {
                $orden->orden_comp_estado = 'APROBADO';
                $orden->save();
            }
        }

        return response()->json([
            'mensaje'  => 'Compra anulada con éxito',
            'tipo'     => 'success',
            'registro' => $compra
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $compra = Compras_cab::find($id);

        if (!$compra) {
            return response()->json(['error' => 'Compra no encontrada'], 404);
        }

        // Validar que no esté ya confirmada
        if ($compra->compra_estado === 'RECIBIDO') {
            return response()->json(['error' => 'Esta compra ya fue confirmada anteriormente'], 400);
        }

        // Marcar compra como RECIBIDO
        $compra->compra_estado = 'RECIBIDO';
        $compra->save();

        // Obtener detalles de compra
        $detalles = Compras_det::where('compra_id', $id)->get();

        // Inicializar acumuladores de totales
        $total_grav_5 = 0;
        $total_grav_10 = 0;
        $total_iva_5 = 0;
        $total_iva_10 = 0;
        $total_exentas = 0;

        foreach ($detalles as $det) {
        $producto = Producto::find($det->producto_id);
        $tipo_imp = Tipo_impuesto::find($producto->impuesto_id);

        $subtotal = $det->compra_cant * $det->compra_costo;

        if ($tipo_imp) {
            switch ($tipo_imp->impuesto_desc) {
                case 'IVA 5%':
                    $base5 = $subtotal / 1.05;
                    $iva5 = $subtotal - $base5;
                    $total_grav_5 += $base5;
                    $total_iva_5 += $iva5;
                    break;

                case 'IVA 10%':
                    $base10 = $subtotal / 1.10;
                    $iva10 = $subtotal - $base10;
                    $total_grav_10 += $base10;
                    $total_iva_10 += $iva10;
                    break;

                case 'EXENTO':
                default:
                    $total_exentas += $subtotal;
                    break;
            }
        }

            // Buscar stock existente
            $stock = Stock::where('deposito_id', 1)
                        ->where('producto_id', $producto->id)
                        ->first();

            if ($stock) {
                $nuevoTotal = $stock->stock_cant_exist + $det->compra_cant;

                if ($nuevoTotal > $stock->stock_cant_max) {
                    $exceso = $nuevoTotal - $stock->stock_cant_max;
                    $stock->stock_cant_exist = $stock->stock_cant_max;
                    $stock->save();

                    Deposito_producto::create([
                        'deposito_id' => 1,
                        'producto_id' => $producto->id,
                        'cantidad' => $exceso,
                        'fecha_movimiento' => now(),
                        'motivo' => 'EXCESO'
                    ]);
                } else {
                    $stock->stock_cant_exist = $nuevoTotal;
                    $stock->save();
                }
            } else {
                // Crear nuevo stock
                Stock::create([
                    'deposito_id' => 1,
                    'producto_id' => $producto->id,
                    'stock_cant_exist' => $det->compra_cant,
                    'stock_cant_min' => 0,
                    'stock_cant_max' => 100
                ]);
            }
        }

            // Guardar totales en cabecera
            $compra->total_grav_5 = $total_grav_5;
            $compra->total_iva_5 = $total_iva_5;
            $compra->total_grav_10 = $total_grav_10;
            $compra->total_iva_10 = $total_iva_10;
            $compra->total_exentas = $total_exentas;
            $compra->total_general = $total_grav_5 + $total_iva_5 + $total_grav_10 + $total_iva_10 + $total_exentas;
            $compra->save();

            // Si es crédito, generar cuentas a pagar
            if ((int) $compra->tipo_fact_id === 7) { // 7 = crédito
                $cuotas = $compra->compra_cant_cta ?? 1;
                $montoPorCuota = $compra->total_general / $cuotas;
                $intervalo = $compra->compra_ifv ?? 30;

                for ($i = 1; $i <= $cuotas; $i++) {
                    Ctas_pagar::create([
                        'id' => $i,
                        'compra_id' => $compra->id,
                        'monto' => $montoPorCuota,
                        'saldo' => $montoPorCuota,
                        'fecha_vencimiento' => now()->addDays($intervalo * $i),
                        'nro_cuota' => $i,
                        'estado' => 'Pendiente',
                        'tipo_fact_id' => $compra->tipo_fact_id
                    ]);
                }
            }

            // Registrar en Libro de Compras
            $primerDetalle = $detalles->first();
            $producto = Producto::find($primerDetalle->producto_id);
            $tipo_imp = Tipo_impuesto::find($producto->impuesto_id);
            $proveedor = Proveedore::find($compra->proveedor_id);

            Libro_Compras::create([
            'compra_id' => $compra->id,
            'lib_comp_fecha' => now(),
            'proveedor_ruc' => $proveedor->proveedor_ruc ?? '',
            'lib_comp_tipo_doc' => null,
            'lib_comp_nro_doc' => $compra->compra_fact,
            'lib_comp_monto' => $compra->total_general,
            'lib_comp_grav_10' => $total_grav_10,
            'lib_comp_iva_10' => $total_iva_10,
            'lib_comp_grav_5' => $total_grav_5,
            'lib_comp_iva_5' => $total_iva_5,
            'lib_comp_exentas' => $total_exentas,
            'proveedor_id' => $proveedor->id,
            'proveedor_desc' => $proveedor->proveedor_desc ?? '',
            'impuesto_id' => $tipo_imp->id ?? null,
            'impuesto_desc' => $tipo_imp->impuesto_desc ?? '',
        ]);

        return response()->json([
            'mensaje' => 'Compra confirmada, stock actualizado, libro y cuenta a pagar generados correctamente.',
            'tipo' => 'success',
            'registro' => $compra
        ], 200);
    }

}
