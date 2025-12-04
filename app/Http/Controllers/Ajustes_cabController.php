<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ajustes_cab;

class Ajustes_cabController extends Controller
{
    public function read()
    {
        return DB::select("
        SELECT 
            ac.*,
            e.empresa_desc,
            s.suc_desc,
            d.deposito_desc,
            to_char(ac.ajuste_fec, 'dd/mm/yyyy') as ajuste_fec,
            u.name as encargado,
            am.ajus_mot_desc
            FROM ajustes_cab ac
            JOIN empresas e ON e.id = ac.empresa_id
            JOIN sucursales s ON s.id = ac.sucursal_id
            JOIN depositos d on d.id = ac.deposito_id
            JOIN users u ON u.id = ac.user_id
            JOIN ajustes_motivos am ON am.id = ac.ajustes_motivos_id
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'empresa_id'            => 'required',
            'sucursal_id'           => 'required',
            'deposito_id'           => 'required',
            'user_id'               => 'required',
            'tipo_ajuste'           => 'required',
            'ajustes_motivos_id'    => 'required',
            'ajuste_fec'            => 'required',
            'monto_exentas'         => 'nullable',
            'monto_iva_5'           => 'nullable',
            'monto_iva_10'          => 'nullable',
            'monto_general'         => 'nullable',
            'ajuste_estado'         => 'required',
        ]);
        
        $datosValidados['ajuste_estado'] = 'PENDIENTE';
        $ajuste = Ajustes_cab::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Compra registrada con éxito',
            'tipo'     => 'success',
            'registro' => $ajuste
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $ajuste = Ajustes_cab::find($id);
        if (!$ajuste) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }
        $datosValidados = $request->validate([
            'empresa_id'            => 'required',
            'sucursal_id'           => 'required',
            'deposito_id'           => 'required',
            'user_id'               => 'required',
            'tipo_ajuste'           => 'required',
            'ajustes_motivos_id'    => 'required',
            'ajuste_fec'            => 'required',
            'monto_exentas'         => 'nullable',
            'monto_iva_5'           => 'nullable',
            'monto_iva_10'          => 'nullable',
            'monto_general'         => 'nullable',
            'ajuste_estado'         => 'required',
        ]);

        $ajuste->update($datosValidados);
        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $ajuste
        ], 200);
    }
    public function anular($id)
    {
        $ajuste = Ajustes_cab::find($id);

        if (!$ajuste || $ajuste->ajuste_estado != 'PENDIENTE') {
            return response()->json([
            'mensaje' => 'No se puede anular el ajuste.',
            'tipo' => 'error'
            ], 400);
        }

        $ajuste->ajuste_estado = 'ANULADO';
        $ajuste->save();

        return response()->json([
            'mensaje' => 'Ajuste anulado correctamente.',
            'tipo' => 'success',
            'registro' => $ajuste
        ], 200);
    }
    public function confirmar($id)
    {
        // Verificar que exista
        $ajuste = Ajustes_cab::find($id);
        if (!$ajuste) {
            return response()->json([
                'mensaje' => 'Ajuste no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($ajuste->ajuste_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar ajustes en estado PENDIENTE.',
                'tipo' => 'error'
            ], 403);
        }

        // Obtener los detalles
        $detalles = DB::table('ajustes_det')->where('ajuste_id', $id)->get();
        if ($detalles->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se puede confirmar un ajuste sin detalles.',
                'tipo' => 'error'
            ], 400);
        }

        $monto_exentas = 0;
        $monto_grav_5 = 0;
        $monto_grav_10 = 0;
        $monto_total = 0;

        foreach ($detalles as $det) {
            // Traer info del producto con su impuesto
            $producto = DB::table('productos as p')
                ->join('tipo_impuestos as ti', 'p.impuesto_id', '=', 'ti.id')
                ->where('p.id', $det->producto_id)
                ->select('p.*', 'ti.id as tipo_imp_id')
                ->first();

            if (!$producto) continue;

            $subtotal = $det->ajuste_cant * $det->ajuste_costo;
            $monto_total += $subtotal;

            switch ($producto->tipo_imp_id) {
                case 2: // 5% IVA
                    $base5 = $subtotal / 1.05;
                    $monto_grav_5 += $base5;
                    break;
                case 1: // 10% IVA
                    $base10 = $subtotal / 1.10;
                    $monto_grav_10 += $base10;
                    break;
                case 3: // Exentas
                default:
                    $monto_exentas += $subtotal;
                    break;
            }

            // ACTUALIZAR STOCK
            $stock = DB::table('stock')
                ->where('deposito_id', $ajuste->deposito_id)
                ->where('sucursal_id', $ajuste->sucursal_id)
                ->where('producto_id', $det->producto_id)
                ->first();

            $cantidad_nueva = $ajuste->tipo_ajuste === 'POSITIVO'
                ? ($stock->stock_cant_exist ?? 0) + $det->ajuste_cant
                : ($stock->stock_cant_exist ?? 0) - $det->ajuste_cant;

            if ($stock) {
                // Actualizar stock existente
                DB::table('stock')->where([
                    ['deposito_id', $ajuste->deposito_id],
                    ['sucursal_id', $ajuste->sucursal_id],
                    ['producto_id', $det->producto_id],
                ])->update([
                    'stock_cant_exist' => $cantidad_nueva,
                    'fecha_movimiento' => now(),
                    'motivo' => 'AJUSTE ' . $ajuste->tipo_ajuste
                ]);
            } else {
                // Insertar nuevo stock
                DB::table('stock')->insert([
                    'deposito_id' => $ajuste->deposito_id,
                    'sucursal_id' => $ajuste->sucursal_id,
                    'producto_id' => $det->producto_id,
                    'stock_cant_exist' => max($cantidad_nueva, 0),
                    'cantidad_exceso' => 0,
                    'fecha_movimiento' => now(),
                    'motivo' => 'AJUSTE ' . $ajuste->tipo_ajuste
                ]);
            }
        }

        // Actualizar cabecera
        $ajuste->update([
            'ajuste_estado'   => 'CONFIRMADO',
            'monto_general'      => $monto_total,
            'monto_exentas'   => $monto_exentas,
            'monto_iva_5'      => $monto_grav_5,
            'monto_iva_10'     => $monto_grav_10
        ]);

        return response()->json([
            'mensaje' => 'Ajuste confirmado exitosamente',
            'tipo'    => 'success',
            'registro' => $ajuste
        ], 200);
    }
}
