<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Remision_comp_cab;
use App\Models\Remision_comp_det;

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
            'rem_comp_nro'          => 'required|string|unique:remision_comp_cab,rem_comp_nro',
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

            $pedido_comp_det = DB::select("
                SELECT 
                pd.producto_id,
                pd.pedido_comp_cant,
                p.prod_precio_comp
                FROM pedidos_comp_det pd
                JOIN productos p ON p.id = pd.producto_id
                WHERE pd.pedido_comp_id = ?
                ", [$request->pedido_comp_id]);

            foreach ($pedido_comp_det as $dp) {
                DB::table('remision_comp_det')->insert([
                    'remision_comp_id' => $remision->id,
                    'producto_id'      => $dp->producto_id,
                    'rem_comp_cant'    => $dp->pedido_comp_cant,
                    'rem_comp_costo'   => $dp->prod_precio_comp,
                    'rem_comp_obs'     => null //se completa desde el front si hace falta
                ]);
            }
        }    
        return response()->json([
            'mensaje'  => 'Nota de remisión registrada con éxito.',
            'tipo'     => 'success',
            'registro' => $remision
        ], 200);
    }
    
    public function update(Request $request, $id)
    {
        $remision = Remision_comp_cab::find($id);

        if (!$remision) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        // Si pedido_comp_id es 0 o vacío, lo convertimos a null
        if ($request->pedido_comp_id === '' || (int)$request->pedido_comp_id === 0) {
            $request->merge(['pedido_comp_id' => null]);
        }

        $datosValidados = $request->validate([
            'pedido_comp_id'        => 'nullable',
            'user_id'               => 'required',
            'sucursal_origen_id'    => 'required',
            'sucursal_destino_id'   => 'required',
            'deposito_origen_id'    => 'required',
            'deposito_destino_id'   => 'required',
            'empresa_id'            => 'required',
            'rem_comp_nro'          => 'required|string|unique:remision_comp_cab,rem_comp_nro,' . $id,
            'remision_motivo_id'    => 'required',
            'rem_comp_fec'          => 'required',
            'rem_comp_fec_sal'      => 'required',
            'rem_comp_fec_recep'    => 'nullable',
            'chofer'                => 'required|string',
            'vehiculo_id'           => 'required',
            'monto_exentas'         => 'nullable',
            'monto_grav_5'          => 'nullable',
            'monto_grav_10'         => 'nullable',
            'monto_iva_5'           => 'nullable',
            'monto_iva_10'          => 'nullable',
            'monto_general'         => 'nullable',
            'rem_comp_estado'       => 'required'
        ]);

        $remision->update($datosValidados);

        return response()->json([
            'mensaje' => 'Remisión modificada con éxito',
            'tipo' => 'success',
            'registro' => $remision
        ], 200);
    }

    public function destroy($id)
    {
        $remision = Remision_comp_cab::find($id);
        if (!$remision) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $remision->delete();
        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }
    public function anular(Request $request, $id)
    {
        $remision = Remision_comp_cab::find($id);

        if (!$remision) {
            return response()->json([
                'mensaje' => 'Remisión no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        $remision->rem_comp_estado = 'ANULADO';
        $remision->user_id = $request->user_id; // Registrar quién anuló
        $remision->save();

        // Si estaba asociada a un pedido, se puede restaurar su estado (opcional) probar si funciona en el front
        if ($remision->pedido_comp_id) {
            $pedido = DB::table('pedidos_comp_cab')
                ->where('id', $remision->pedido_comp_id)
                ->first();

            if ($pedido) {
                DB::table('pedidos_comp_cab')
                    ->where('id', $pedido->id)
                    ->update(['pedido_comp_estado' => 'APROBADO']);
            }
        }

        return response()->json([
            'mensaje' => 'Remisión anulada con éxito',
            'tipo' => 'success',
            'registro' => $remision
        ], 200);
    }
        public function confirmar(Request $r, $id)
    {
        $remision = Remision_comp_cab::find($id);

        if (!$remision) {
            return response()->json(['error' => 'Remisión no encontrada'], 404);
        }

        // Validar que no esté ya confirmada
        if ($remision->rem_comp_estado === 'CONFIRMADO') {
            return response()->json(['error' => 'Esta remisión ya fue confirmada anteriormente'], 400);
        }

        // Validar que venga la fecha de recepción
        if (!$r->filled('rem_comp_fec_recep')) {
            return response()->json(['error' => 'Debe registrar la fecha de recepción para confirmar la remisión'], 422);
        }

        // Obtener detalles
        $detalles = Remision_comp_det::where('remision_comp_id', $id)->get();

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

            $subtotal = $det->rem_comp_cant * $det->rem_comp_costo;

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

            // ACTUALIZAR STOCK - ORIGEN (restar)
            $stock_origen = DB::table('stock')
                ->where('deposito_id', $remision->deposito_origen_id)
                ->where('sucursal_id', $remision->sucursal_origen_id)
                ->where('producto_id', $producto->id)
                ->first();

            if ($stock_origen) {
                $nueva_cantidad = max(0, $stock_origen->stock_cant_exist - $det->rem_comp_cant);
                DB::table('stock')
                    ->where('deposito_id', $remision->deposito_origen_id)
                    ->where('sucursal_id', $remision->sucursal_origen_id)
                    ->where('producto_id', $producto->id)
                    ->update([
                        'stock_cant_exist' => $nueva_cantidad,
                        'fecha_movimiento' => $r->rem_comp_fec_recep,
                        'motivo' => 'SALIDA POR REMISIÓN'
                    ]);
            }

            // ACTUALIZAR STOCK - DESTINO (sumar)
            $stock_destino = DB::table('stock')
                ->where('deposito_id', $remision->deposito_destino_id)
                ->where('sucursal_id', $remision->sucursal_destino_id)
                ->where('producto_id', $producto->id)
                ->first();

            if ($stock_destino) {
                $nuevo_total = $stock_destino->stock_cant_exist + $det->rem_comp_cant;

                if ($nuevo_total > $stock_destino->stock_cant_max) {
                    $exceso = $nuevo_total - $stock_destino->stock_cant_max;

                    DB::table('stock')
                        ->where('deposito_id', $remision->deposito_destino_id)
                        ->where('sucursal_id', $remision->sucursal_destino_id)
                        ->where('producto_id', $producto->id)
                        ->update([
                            'stock_cant_exist' => $stock_destino->stock_cant_max,
                            'cantidad_exceso' => $stock_destino->cantidad_exceso + $exceso,
                            'fecha_movimiento' => $r->rem_comp_fec_recep,
                            'motivo' => 'EXCESO RECEPCIÓN REMISIÓN'
                        ]);
                } else {
                    DB::table('stock')
                        ->where('deposito_id', $remision->deposito_destino_id)
                        ->where('sucursal_id', $remision->sucursal_destino_id)
                        ->where('producto_id', $producto->id)
                        ->update([
                            'stock_cant_exist' => $nuevo_total,
                            'fecha_movimiento' => $r->rem_comp_fec_recep,
                            'motivo' => 'ENTRADA REMISIÓN'
                        ]);
                }
            } else {
                // Insertar nuevo registro en stock
                DB::table('stock')->insert([
                    'deposito_id' => $remision->deposito_destino_id,
                    'sucursal_id' => $remision->sucursal_destino_id,
                    'producto_id' => $producto->id,
                    'stock_cant_exist' => $det->rem_comp_cant,
                    'stock_cant_min' => 0,
                    'stock_cant_max' => 100,
                    'cantidad_exceso' => 0,
                    'fecha_movimiento' => $r->rem_comp_fec_recep,
                    'motivo' => 'ENTRADA REMISIÓN'
                ]);
            }
        }

        // Actualizar totales y estado
        $remision->rem_comp_fec_recep = $r->rem_comp_fec_recep;
        $remision->rem_comp_estado = 'CONFIRMADO';
        $remision->monto_grav_5 = $monto_grav_5;
        $remision->monto_iva_5 = $monto_iva_5;
        $remision->monto_grav_10 = $monto_grav_10;
        $remision->monto_iva_10 = $monto_iva_10;
        $remision->monto_exentas = $monto_exentas;
        $remision->monto_general = $monto_grav_5 + $monto_iva_5 + $monto_grav_10 + $monto_iva_10 + $monto_exentas;
        $remision->save();

        return response()->json([
            'mensaje' => 'Remisión confirmada y stock actualizado correctamente.',
            'tipo' => 'success',
            'registro' => $remision
        ], 200);
    }

}
