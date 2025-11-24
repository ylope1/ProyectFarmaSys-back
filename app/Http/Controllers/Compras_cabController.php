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
use App\Models\Deposito;
use App\Models\Ctas_pagar;
use App\Models\Libro_compras;
use App\Models\Proveedore;
use Illuminate\Support\Facades\DB;

class Compras_cabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT 
                cc.*,
                e.empresa_desc,
                s.suc_desc,
                d.deposito_desc,
                to_char(cc.compra_fec, 'dd/mm/yyyy') as compra_fec,
                to_char(cc.compra_fec_recep, 'dd/mm/yyyy') as compra_fec_recep,
                p.proveedor_desc,
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
                JOIN depositos d on d.id = cc.deposito_id
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
            'deposito_id'          => 'required',
            'tipo_fact_id'          => 'required',
            'compra_fact'           => 'required|string',
            'compra_timbrado'       => 'required|integer',
            'compra_fec'            => 'required',
            'compra_fec_recep'      => 'required',
            'compra_cant_cta'       => 'nullable|integer',
            'compra_ifv'            => 'nullable|integer',
            'compra_estado'         => 'required|string',
            'monto_exentas'         => 'nullable|numeric',
            'monto_grav_5'          => 'nullable|numeric',
            'monto_grav_10'         => 'nullable|numeric',
            'monto_iva_5'           => 'nullable|numeric',
            'monto_iva_10'          => 'nullable|numeric',
            'monto_general'         => 'nullable|numeric',
        ]);
                // 🔄 Corregir orden_comp_id = 0 a null
        if ((int) $request->orden_comp_id === 0) {
            $datosValidados['orden_comp_id'] = null;
}
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
            'deposito_id'          => 'required',
            'tipo_fact_id'          => 'required',
            'compra_fact'           => 'required|string',
            'compra_timbrado'       => 'required|integer',
            'compra_fec'            => 'required',
            'compra_fec_recep'      => 'required',
            'compra_cant_cta'       => 'nullable|integer',
            'compra_ifv'            => 'nullable|integer',
            'compra_estado'         => 'required|string',
            'monto_exentas'         => 'nullable|numeric',
            'monto_grav_5'          => 'nullable|numeric',
            'monto_grav_10'         => 'nullable|numeric',
            'monto_iva_5'           => 'nullable|numeric',
            'monto_iva_10'          => 'nullable|numeric',
            'monto_general'         => 'nullable|numeric',
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
            $stock = Stock::where('deposito_id', $compra->deposito_id)
                        ->where('sucursal_id', $compra->sucursal_id)
                        ->where('producto_id', $producto->id)
                        ->first();

            if ($stock) {
                $nuevoTotal = $stock->stock_cant_exist + $det->compra_cant;

                if ($nuevoTotal > $stock->stock_cant_max) {
                    $exceso = $nuevoTotal - $stock->stock_cant_max;

                    $stock->stock_cant_exist = $stock->stock_cant_max;
                    $stock->cantidad_exceso += $exceso;
                    $stock->fecha_movimiento =  $compra->compra_fec_recep; 
                    $stock->motivo = 'EXCESO RECEPCIÓN COMPRA';
                    $stock->save();

                } else {
                    $stock->stock_cant_exist = $nuevoTotal;
                    $stock->fecha_movimiento =  $compra->compra_fec_recep;
                    $stock->motivo = 'ENTRADA COMPRA';
                    $stock->save();
                }
            } else {
                // Crear nuevo stock
                Stock::create([
                    'deposito_id' => $compra->deposito_id,
                    'sucursal_id' => $compra->sucursal_id,
                    'producto_id' => $producto->id,
                    'stock_cant_exist' => $det->compra_cant,
                    'stock_cant_min' => 0,
                    'stock_cant_max' => 100,
                    'cantidad_exceso' => 0,
                    'fecha_movimiento' =>  $compra->compra_fec_recep,
                    'motivo' => 'ENTRADA COMPRA'
                ]);
            }
        }

            // Guardar totales en cabecera
            $compra->monto_grav_5 = $monto_grav_5;
            $compra->monto_iva_5 = $monto_iva_5;
            $compra->monto_grav_10 = $monto_grav_10;
            $compra->monto_iva_10 = $monto_iva_10;
            $compra->monto_exentas = $monto_exentas;
            $compra->monto_general = $monto_grav_5 + $monto_iva_5 + $monto_grav_10 + $monto_iva_10 + $monto_exentas;
            $compra->save();

            // Si es crédito, generar cuentas a pagar
            if ((int) $compra->tipo_fact_id === 7) { // 7 = crédito
                $cuotas = $compra->compra_cant_cta ?? 1;
                $montoPorCuota = $compra->monto_general / $cuotas;
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
            $producto = DB::table('productos as p')
                ->join('tipo_impuestos as ti', 'p.impuesto_id', '=', 'ti.id')
                ->where('p.id', $primerDetalle->producto_id)
                ->select('p.*', 'ti.id as tipo_imp_id', 'ti.impuesto_desc as tipo_imp_desc')
                ->first();
            $proveedor = Proveedore::find($compra->proveedor_id);

            Libro_Compras::create([
            'compra_id' => $compra->id,
            'lib_comp_fecha' => $compra->compra_fec,
            'proveedor_ruc' => $proveedor->proveedor_ruc ?? '', 
            'lib_comp_tipo_doc' => 'FACTURA',
            'lib_comp_nro_doc' => $compra->compra_fact,
            'lib_comp_monto' => $compra->monto_general,
            'lib_comp_grav_10' => $monto_grav_10,
            'lib_comp_iva_10' => $monto_iva_10,
            'lib_comp_grav_5' => $monto_grav_5,
            'lib_comp_iva_5' => $monto_iva_5,
            'lib_comp_exentas' => $monto_exentas,
            'proveedor_id' => $proveedor->id,
            'proveedor_desc' => $proveedor->proveedor_desc ?? '',
            'impuesto_id' => $producto->tipo_imp_id ?? null, 
            'impuesto_desc' => $producto->tipo_imp_desc ?? '', 
        ]);

        return response()->json([
            'mensaje' => 'Compra confirmada, stock actualizado, libro y cuenta a pagar generados correctamente.',
            'tipo' => 'success',
            'registro' => $compra
        ], 200);
    }
    public function buscar(Request $r){
        return DB::select("SELECT 
            cc.id,
            to_char(cc.compra_fec, 'dd/mm/yyyy HH24:mi:ss') AS compra_fec,
            cc.compra_estado,
            cc.empresa_id,  
            e.empresa_desc,
            cc.sucursal_id, 
            s.suc_desc,
            cc.proveedor_id,
            p.proveedor_desc,
            cc.user_id, 
            u.name AS encargado,
            cc.id as compra_id,
            'COMPRA NRO:' || to_char(cc.id, '0000000') || 
            ' FECHA: ' || to_char(cc.compra_fec, 'dd/mm/yyyy HH24:mi:ss') || 
            ' (' || cc.compra_estado || ')' AS compra
        FROM compras_cab cc 
        JOIN empresas e ON e.id = cc.empresa_id
        JOIN sucursales s ON s.id = cc.sucursal_id 
        JOIN proveedores p ON p.id = cc.proveedor_id
        JOIN users u ON u.id = cc.user_id 
        WHERE cc.compra_estado = 'CONFIRMADO' 
        AND cc.user_id = ? 
        AND u.name ILIKE ?
        ", [$r->user_id, '%' . $r->name . '%']);
    }
}
