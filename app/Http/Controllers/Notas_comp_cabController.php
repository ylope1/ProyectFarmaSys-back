<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notas_comp_cab;
use App\Models\Notas_comp_det;
use App\Models\Compras_cab;
use App\Models\Stock;
use App\Models\Proveedore;
use App\Models\Libro_compras;
use App\Models\Ctas_pagar;
use App\Models\Producto;

class Notas_comp_cabController extends Controller
{
    public function read()
    {
        return DB::select("SELECT
            ncc.*,
            p.proveedor_desc,
            e.empresa_desc,
            s.suc_desc,
            d.deposito_desc,
            tf.tipo_fact_desc,
            u.name as encargado,
            to_char(ncc.nota_comp_fec, 'dd/mm/yyyy') as nota_comp_fec,
            COALESCE(
                'FACTURA: ' || cc.compra_fact || 
                ' - FECHA: ' || to_char(cc.compra_fec, 'dd/mm/yyyy') ||
                ' - ESTADO: ' || cc.compra_estado
            ) AS compra
            FROM notas_comp_cab ncc
            JOIN compras_cab cc ON cc.id = ncc.compra_id
            JOIN proveedores p ON p.id = ncc.proveedor_id
            JOIN empresas e ON e.id = ncc.empresa_id
            JOIN sucursales s ON s.id = ncc.sucursal_id
            JOIN depositos d ON d.id = ncc.deposito_id
            JOIN tipo_fact tf ON tf.id = ncc.tipo_fact_id
            JOIN users u ON u.id = ncc.user_id");
    }

    public function store(Request $request)
    {
        // Verificar duplicados PRIMERO
        $existe = Notas_comp_cab::where('compra_id', $request->compra_id)
                ->where('nota_comp_tipo', $request->nota_comp_tipo)
                ->exists();
            if ($existe) {
               return response()->json([
                'mensaje' => 'Ya existe una nota de este tipo para esta compra.',
                'tipo' => 'error'
            ], 400);
        }

        $datos = $request->validate([
            'compra_id' => 'required|exists:compras_cab,id',
            'proveedor_id' => 'required',
            'user_id' => 'required',
            'deposito_id' => 'required',
            'sucursal_id' => 'required',
            'empresa_id' => 'required',
            'tipo_fact_id' => 'required',
            'nota_comp_tipo' => 'required|in:NC,ND',
            'nota_comp_fact' => 'required|string',
            'nota_comp_timbrado'=> 'required|integer',
            'nota_comp_fec' => 'required|date',
            'nota_comp_estado' => 'required|in:PENDIENTE,CONFIRMADO,ANULADO',
            'monto_exentas' => 'nullable|numeric',
            'monto_grav_5' => 'nullable|numeric',
            'monto_grav_10' => 'nullable|numeric',
            'monto_iva_5' => 'nullable|numeric',
            'monto_iva_10' => 'nullable|numeric',
            'monto_general' => 'nullable|numeric',
        ]);
        // Verificar que la compra esté en estado RECIBIDO
        $compra = Compras_cab::find($request->compra_id);
        if (!$compra || $compra->compra_estado !== 'RECIBIDO') {
            return response()->json([
                'mensaje' => 'Solo se pueden emitir notas sobre compras confirmadas (RECIBIDO).',
                'tipo' => 'error'
            ], 400);
        }
        // Crear la nota de compra
        $nota = Notas_comp_cab::create($datos);

        // Copiar detalles de la compra como base para la nota (editable luego)
        $detalles = DB::table('compras_det')
            ->where('compra_id', $compra->id)
            ->get();

        foreach ($detalles as $det) {
            DB::table('notas_comp_det')->insert([
                'nota_comp_id'     => $nota->id,
                'producto_id'      => $det->producto_id,
                'compra_cant'      => $det->compra_cant,
                'compra_costo'     => $det->compra_costo,
                'nota_comp_motivo' => 'Pendiente de definición'
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
        $nota = Notas_comp_cab::find($id);
        if (!$nota || $nota->nota_comp_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Nota no editable', 
                'tipo' => 'error'
            ], 400);
        }

        $datos = $request->validate([
            'nota_comp_fact' => 'required|string',
            'nota_comp_timbrado'=> 'required|integer',
            'nota_comp_fec' => 'required|date',
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
        $nota = Notas_comp_cab::find($id);
        if (!$nota) {
            return response()->json([
                'mensaje' => 'Nota no encontrada', 
                'tipo' => 'error']
                , 404);
        }
            
        // Solo se permite anular si está pendiente
        if ($nota->nota_comp_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden anular notas en estado PENDIENTE.',
                'tipo' => 'error'
            ], 400);
        }
        $nota->nota_comp_estado = 'ANULADO';
        $nota->user_id = $request->user_id;
        $nota->save();

        return response()->json([
            'mensaje' => 'Nota anulada', 
            'tipo' => 'success'
        ],200);
    }

    public function confirmar($id)
    {
        $nota = Notas_comp_cab::find($id);

        if (!$nota || $nota->nota_comp_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'No se puede confirmar', 
                'tipo' => 'error'
            ], 400);
        }

        $detalles = Notas_comp_det::where('nota_comp_id', $id)->get();

        foreach ($detalles as $det) {
            $stock = Stock::where('deposito_id', $nota->deposito_id)
                    ->where('sucursal_id', $nota->sucursal_id)
                    ->where('producto_id', $det->producto_id)
                    ->first();

            if ($stock) {
                $ajuste = ($nota->nota_comp_tipo === 'NC') ? -$det->compra_cant : $det->compra_cant;
                $stock->stock_cant_exist += $ajuste;
                $stock->fecha_movimiento = $nota->nota_comp_fec;
                $stock->motivo = 'AJUSTE NOTA ' . ($nota->nota_comp_tipo === 'NC' ? 'CRÉDITO' : 'DÉBITO');
                $stock->save();
            }
        }

        // Insertar en Libro de Compras si es CREDITO
        //if ($nota->nota_comp_tipo === 'NC') {
        $proveedor = Proveedore::find($nota->proveedor_id);

            Libro_compras::create([
                'compra_id' => $nota->compra_id,
                'lib_comp_fecha' => $nota->nota_comp_fec,
                'proveedor_ruc' => $proveedor->proveedor_ruc ?? '',
                'lib_comp_tipo_doc' => $nota->nota_comp_tipo, // NC o ND
                'lib_comp_nro_doc' => $nota->nota_comp_fact,
                'lib_comp_monto' => $nota->monto_general,
                'lib_comp_grav_10' => $nota->monto_grav_10,
                'lib_comp_iva_10' => $nota->monto_iva_10,
                'lib_comp_grav_5' => $nota->monto_grav_5,
                'lib_comp_iva_5' => $nota->monto_iva_5,
                'lib_comp_exentas' => $nota->monto_exentas,
                'proveedor_id' => $proveedor->id,
                'proveedor_desc' => $proveedor->proveedor_desc ?? '',
                'impuesto_id' => null,
                'impuesto_desc' => '',
            ]);
        //}

        // AJUSTAR CUENTAS A PAGAR si la compra fue a CRÉDITO
        $compra = Compras_cab::find($nota->compra_id);

        if ($compra && (int)$compra->tipo_fact_id === 7) { // 7 = crédito
            $cuentas = Ctas_pagar::where('compra_id', $nota->compra_id)->get();
            $montoParcial = $nota->monto_general / max($cuentas->count(), 1);

            foreach ($cuentas as $cuenta) {
                if ($nota->nota_comp_tipo === 'NC') {
                $cuenta->saldo -= $montoParcial;
                } elseif ($nota->nota_comp_tipo === 'ND') {
                $cuenta->saldo += $montoParcial;
                }
                $cuenta->save();
            }
        }

        $nota->nota_comp_estado = 'CONFIRMADO';
        $nota->save();

        return response()->json([
            'mensaje' => 'Nota confirmada y aplicada correctamente.', 
            'tipo' => 'success'
        ],200);
    }
}
