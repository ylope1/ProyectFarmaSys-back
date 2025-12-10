<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notas_venta_cab;
use App\Models\Notas_venta_det;
use App\Models\Ventas_cab;
use App\Models\Stock;
use App\Models\Clientes;
use App\Models\Libro_Ventas;
use App\Models\Ctas_cobrar;
use App\Models\Producto;

class Notas_venta_cabController extends Controller
{
    public function read() 
    {
        return DB::select("SELECT
            nvc.*,
            vc.cliente_id,
            per.pers_nombre||' '||per.pers_apellido as nombre_cliente,
            per.pers_ci as cliente_ci,
            cl.cli_ruc,
            e.empresa_desc,
            s.suc_desc,
            d.deposito_desc,
            tf.tipo_fact_desc,
            u.name as vendedor,
            to_char(nvc.nota_vent_fec, 'dd/mm/yyyy HH24:mi:ss') as nota_vent_fec,
            COALESCE(
                'FACTURA: ' || vc.venta_fact || 
                ' - FECHA: ' || to_char(vc.venta_fec, 'dd/mm/yyyy HH24:mi:ss') ||
                ' - ESTADO: ' || vc.venta_estado
            ) AS venta
            FROM notas_venta_cab nvc
            JOIN ventas_cab vc ON vc.id = nvc.venta_id
            JOIN clientes cl ON nvc.cliente_id = cl.id
            JOIN personas per ON cl.persona_id = per.id
            JOIN empresas e ON e.id = nvc.empresa_id
            JOIN sucursales s ON s.id = nvc.sucursal_id
            JOIN depositos d ON d.id = nvc.deposito_id
            JOIN tipo_fact tf ON tf.id = nvc.tipo_fact_id
            JOIN users u ON u.id = nvc.user_id");
    }

    public function store(Request $request)
    {
        // Verificar duplicados PRIMERO
        $existe = Notas_venta_cab::where('venta_id', $request->venta_id)
                ->where('nota_vent_tipo', $request->nota_vent_tipo)
                ->exists();
            if ($existe) {
               return response()->json([
                'mensaje' => 'Ya existe una nota de este tipo para esta venta.',
                'tipo' => 'error'
            ], 400);
        }

        $datos = $request->validate([
            'venta_id' => 'required|exists:ventas_cab,id',
            'cliente_id' => 'required',
            'user_id' => 'required',
            'deposito_id' => 'required',
            'sucursal_id' => 'required',
            'empresa_id' => 'required',
            'tipo_fact_id' => 'required',
            'nota_vent_tipo' => 'required|in:NC,ND',
            'nota_vent_fact' => 'required|string',
            'nota_vent_timbrado'=> 'required|integer',
            'nota_vent_fec' => 'required',
            'nota_vent_estado' => 'required|in:PENDIENTE,CONFIRMADO,ANULADO',
            'monto_exentas' => 'nullable|numeric',
            'monto_grav_5' => 'nullable|numeric',
            'monto_grav_10' => 'nullable|numeric',
            'monto_iva_5' => 'nullable|numeric',
            'monto_iva_10' => 'nullable|numeric',
            'monto_general' => 'nullable|numeric',
        ]);
        // Verificar que la venta esté en estado CONFIRMADO
        $venta = Ventas_cab::find($request->venta_id);
        if (!$venta || $venta->venta_estado !== 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden emitir notas sobre ventas confirmadas.',
                'tipo' => 'error'
            ], 400);
        }
        // Crear la nota de venta
        $nota = Notas_venta_cab::create($datos);

        // Copiar detalles de la venta como base para la nota (editable luego)
        $detalles = DB::table('ventas_det')
            ->where('venta_id', $venta->id)
            ->get();

        foreach ($detalles as $det) {
            DB::table('notas_venta_det')->insert([
                'nota_venta_id'     => $nota->id,
                'producto_id'      => $det->producto_id,
                'nota_venta_cant'      => $det->venta_cant,
                'nota_venta_precio'     => $det->venta_precio,
                'nota_venta_motivo' => 'Cambio de Producto'
            ]);
        }

        return response()->json([
            'mensaje' => 'Nota registrada correctamente.',
            'tipo' => 'success',
            'registro' => $nota
        ], 200);

    }

    public function update(Request $request, $id)
    {
        $nota = Notas_venta_cab::find($id);
        if (!$nota || $nota->nota_vent_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Nota no editable', 
                'tipo' => 'error'
            ], 400);
        }

        $datos = $request->validate([
            'nota_vent_fact' => 'required|string',
            'nota_vent_timbrado'=> 'required|integer',
            'nota_vent_fec' => 'required',
            'monto_exentas' => 'nullable|numeric',
            'monto_grav_5' => 'nullable|numeric',
            'monto_grav_10' => 'nullable|numeric',
            'monto_iva_5' => 'nullable|numeric',
            'monto_iva_10' => 'nullable|numeric',
            'monto_general' => 'nullable|numeric',
        ]);

        $nota->update($datos);
        return response()->json([
            'mensaje' => 'Nota modificada', 
            'tipo' => 'success', 
            'registro' => $nota
        ],200);
    }

    public function anular(Request $request, $id)
    {
        $nota = Notas_venta_cab::find($id);
        if (!$nota) {
            return response()->json([
                'mensaje' => 'Nota no encontrada', 
                'tipo' => 'error']
                , 404);
        }
            
        // Solo se permite anular si está pendiente
        if ($nota->nota_vent_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden anular notas en estado PENDIENTE.',
                'tipo' => 'error'
            ], 400);
        }
        $nota->nota_venta_estado = 'ANULADO';
        $nota->user_id = $request->user_id;
        $nota->save();

        return response()->json([
            'mensaje' => 'Nota anulada', 
            'tipo' => 'success'
        ],200);
    }

    public function confirmar($id)
    {
        $nota = Notas_venta_cab::find($id);

        if (!$nota || $nota->nota_vent_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'No se puede confirmar', 
                'tipo' => 'error'
            ], 400);
        }

        $detalles = Notas_venta_det::where('nota_venta_id', $id)->get();
        if ($detalles->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se puede confirmar una nota sin detalles.',
                'tipo' => 'error'
            ], 400);
        }

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

        $subtotal = $det->nota_venta_cant * $det->nota_venta_precio;

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

            // Ajustar Stock
            $stock = Stock::where('deposito_id', $nota->deposito_id)
                    ->where('sucursal_id', $nota->sucursal_id)
                    ->where('producto_id', $det->producto_id)
                    ->first();

            if ($stock) {
                $ajuste = ($nota->nota_vent_tipo === 'NC') ? -$det->nota_venta_cant : $det->nota_venta_cant;
                $stock->stock_cant_exist += $ajuste;
                $stock->fecha_movimiento = $nota->nota_vent_fec;
                $stock->motivo = 'AJUSTE NOTA ' . ($nota->nota_vent_tipo === 'NC' ? 'CRÉDITO' : 'DÉBITO');
                $stock->save();
            }
        }
            // Actualizar totales en la nota
        $nota->monto_grav_5 = $monto_grav_5;
        $nota->monto_grav_10 = $monto_grav_10;
        $nota->monto_iva_10 = $monto_iva_10;
        $nota->monto_iva_5 = $monto_iva_5;
        $nota->monto_exentas = $monto_exentas;
        $nota->monto_general = $monto_grav_10 + $monto_grav_5 + $monto_exentas + $monto_iva_10 + $monto_iva_5;
        $nota->save(); 

        // Insertar en Libro de Ventas si es CREDITO
        $primerDetalle = $detalles->first();
        $producto = DB::table('productos as p')
            ->join('tipo_impuestos as ti', 'p.impuesto_id', '=', 'ti.id')
            ->where('p.id', $primerDetalle->producto_id)
            ->select('ti.id as tipo_imp_id', 'ti.impuesto_desc as tipo_imp_desc')
            ->first();
        $clientes = DB::table('clientes as c')
            ->join('personas as p', 'c.persona_id', '=', 'p.id')
            ->where('c.id', $nota->cliente_id)
            ->select('c.id as cliente_id', 'c.cli_ruc', 
                DB::raw("p.pers_nombre || ' ' || p.pers_apellido as cliente_nombre"))
            ->first();

        Libro_Ventas::create([ 
            'venta_id' => $nota->venta_id,
            'lib_vent_fecha' => $nota->nota_vent_fec,
            'cli_ruc' => $clientes->cli_ruc ?? '',
            'lib_vent_tipo_doc' => $nota->nota_vent_tipo, // NC o ND
            'lib_vent_nro_doc' => $nota->nota_vent_fact,
            'lib_vent_monto' => $nota->monto_general,
            'lib_vent_grav_10' => $nota->monto_grav_10,
            'lib_vent_iva_10' => $nota->monto_iva_10,
            'lib_vent_grav_5' => $nota->monto_grav_5,
            'lib_vent_iva_5' => $nota->monto_iva_5,
            'lib_vent_exentas' => $nota->monto_exentas,
            'cliente_id' => $clientes->cliente_id, 
            'cliente_nombre' => $clientes->cliente_nombre ?? '',
            'impuesto_id' => $producto->tipo_imp_id ?? null,
            'impuesto_desc' => $producto->tipo_imp_desc ?? '',
        ]);

         // AJUSTAR CUENTAS A COBRAR si la venta fue a CRÉDITO
        $venta = Ventas_cab::find($nota->venta_id);

        if ($venta && (int)$venta->tipo_fact_id === 7) { // 7 = crédito
            $cuentas = Ctas_cobrar::where('venta_id', $nota->venta_id)->get();
            
            if ($cuentas->count() > 0) {
                $montoParcial = $nota->monto_general / $cuentas->count();
                
                foreach ($cuentas as $cuenta) {
                    if ($nota->nota_vent_tipo === 'NC') {
                        $cuenta->ctas_cob_saldo -= $montoParcial; // O el nombre correcto del campo
                    } elseif ($nota->nota_vent_tipo === 'ND') {
                        $cuenta->ctas_cob_saldo += $montoParcial; // O el nombre correcto del campo
                    }
                    $cuenta->save();
                }
            }
        }

        $nota->nota_vent_estado = 'CONFIRMADO';
        $nota->save();

        return response()->json([
            'mensaje' => 'Nota confirmada y aplicada correctamente.', 
            'tipo' => 'success',
            'registro' => $nota  
        ],200);
    }
}
