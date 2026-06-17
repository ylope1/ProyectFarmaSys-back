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
use App\Traits\VerificaPermisos;

class Notas_comp_cabController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'movimientos/compras/notas_cred_deb/';

    public function read()
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("SELECT
            ncc.*,
            p.proveedor_desc,
            e.empresa_desc,
            s.suc_desc,
            d.deposito_desc,
            tf.tipo_fact_desc,
            u.name as encargado,
            to_char(ncc.nota_comp_fec, 'dd/mm/yyyy HH24:mi:ss') as nota_comp_fec,
            COALESCE(
                'FACTURA: ' || cc.compra_fact || 
                ' - FECHA: ' || to_char(cc.compra_fec, 'dd/mm/yyyy HH24:mi:ss') ||
                ' - ESTADO: ' || cc.compra_estado
            ) AS compra
        FROM notas_comp_cab ncc
        JOIN compras_cab cc ON cc.id = ncc.compra_id
        JOIN proveedores p ON p.id = ncc.proveedor_id
        JOIN empresas e ON e.id = ncc.empresa_id
        JOIN sucursales s ON s.id = ncc.sucursal_id
        JOIN depositos d ON d.id = ncc.deposito_id
        JOIN tipo_fact tf ON tf.id = ncc.tipo_fact_id
        JOIN users u ON u.id = ncc.user_id
        ORDER BY ncc.id DESC");
    }

    public function store(Request $request)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'mensaje' => 'Usuario no autenticado.',
                'tipo' => 'error'
            ], 401);
        }

        $datos = $request->validate([
            'compra_id' => 'required|exists:compras_cab,id',
            'nota_comp_tipo' => 'required|in:NC,ND',
            'nota_comp_fact' => 'required|string|max:50',
            'nota_comp_timbrado' => 'required|integer',
            'nota_comp_fec' => 'required',
        ]);

        return DB::transaction(function () use ($datos, $user) {

            $compra = Compras_cab::find($datos['compra_id']);

            if (!$compra || $compra->compra_estado !== 'RECIBIDO') {
                return response()->json([
                    'mensaje' => 'Solo se pueden registrar notas sobre compras en estado RECIBIDO.',
                    'tipo' => 'error'
                ], 400);
            }

            $existeDocumento = Notas_comp_cab::where('proveedor_id', $compra->proveedor_id)
                ->where('nota_comp_tipo', $datos['nota_comp_tipo'])
                ->where('nota_comp_fact', $datos['nota_comp_fact'])
                ->where('nota_comp_timbrado', $datos['nota_comp_timbrado'])
                ->where('nota_comp_estado', '<>', 'ANULADO')
                ->exists();

            if ($existeDocumento) {
                return response()->json([
                    'mensaje' => 'Ya existe una nota activa con el mismo número, timbrado, tipo y proveedor.',
                    'tipo' => 'error'
                ], 400);
            }

            // Copiar detalles de la compra como base para la nota (editable luego)
            $detallesCompra = DB::table('compras_det')
                ->where('compra_id', $compra->id)
                ->get();

            if ($detallesCompra->isEmpty()) {
                return response()->json([
                    'mensaje' => 'La compra seleccionada no posee detalle.',
                    'tipo' => 'error'
                ], 400);
            }

            $nota = Notas_comp_cab::create([
                'compra_id' => $compra->id,
                'proveedor_id' => $compra->proveedor_id,
                'user_id' => $user->id,
                'deposito_id' => $compra->deposito_id,
                'sucursal_id' => $compra->sucursal_id,
                'empresa_id' => $compra->empresa_id,
                'tipo_fact_id' => $compra->tipo_fact_id,
                'nota_comp_tipo' => $datos['nota_comp_tipo'],
                'nota_comp_fact' => $datos['nota_comp_fact'],
                'nota_comp_timbrado' => $datos['nota_comp_timbrado'],
                'nota_comp_fec' => $datos['nota_comp_fec'],
                'nota_comp_estado' => 'PENDIENTE',
            ]);

            foreach ($detallesCompra as $det) {
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
        });
    }

    public function update(Request $request, $id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $user = auth()->user();

        $nota = Notas_comp_cab::find($id);

        if (!$nota) {
            return response()->json([
                'mensaje' => 'Nota no encontrada.',
                'tipo' => 'error'
            ], 404);
        }
        
        if ($nota->nota_comp_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden modificar notas en estado PENDIENTE.',
                'tipo' => 'error'
            ], 400);
        }

        $datos = $request->validate([
            'nota_comp_fact' => 'required|string|max:50',
            'nota_comp_timbrado' => 'required|integer',
            'nota_comp_fec' => 'required',
        ]);

        $existeDocumento = Notas_comp_cab::where('id', '<>', $id)
            ->where('proveedor_id', $nota->proveedor_id)
            ->where('nota_comp_tipo', $nota->nota_comp_tipo)
            ->where('nota_comp_fact', $datos['nota_comp_fact'])
            ->where('nota_comp_timbrado', $datos['nota_comp_timbrado'])
            ->where('nota_comp_estado', '<>', 'ANULADO')
            ->exists();

        if ($existeDocumento) {
            return response()->json([
                'mensaje' => 'Ya existe otra nota activa con el mismo número, timbrado, tipo y proveedor.',
                'tipo' => 'error'
            ], 400);
        }

        $nota->nota_comp_fact = $datos['nota_comp_fact'];
        $nota->nota_comp_timbrado = $datos['nota_comp_timbrado'];
        $nota->nota_comp_fec = $datos['nota_comp_fec'];
        $nota->user_id = $user->id;
        $nota->save();

        return response()->json([
            'mensaje' => 'Nota modificada', 
            'tipo' => 'success', 
            'registro' => $nota
        ],200);
    }

    public function anular(Request $request, $id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular');
        if ($permiso) {
            return $permiso;
        }

        $user = auth()->user();

        return DB::transaction(function () use ($id, $user) {

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
            $nota->user_id = $user->id;
            $nota->save();

            return response()->json([
                'mensaje' => 'Nota anulada correctamente', 
                'tipo' => 'success'
            ],200);
        });
    }

    public function confirmar($id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'confirmar');
        if ($permiso) {
            return $permiso;
        }

        $user = auth()->user();

        return DB::transaction(function () use ($id, $user) {

            $nota = Notas_comp_cab::find($id);

            if (!$nota) {
                return response()->json([
                    'mensaje' => 'Nota no encontrada.',
                    'tipo' => 'error'
                ], 404);
            }

            if ($nota->nota_comp_estado !== 'PENDIENTE') {
                return response()->json([
                    'mensaje' => 'Solo se pueden confirmar notas en estado PENDIENTE.',
                    'tipo' => 'error'
                ], 400);
            }

            $compra = Compras_cab::find($nota->compra_id);

            if (!$compra || $compra->compra_estado !== 'RECIBIDO') {
                return response()->json([
                    'mensaje' => 'La compra origen debe estar en estado RECIBIDO.',
                    'tipo' => 'error'
                ], 400);
            }

            $detalles = Notas_comp_det::where('nota_comp_id', $nota->id)->get();

            if ($detalles->isEmpty()) {
                return response()->json([
                    'mensaje' => 'No se puede confirmar una nota sin detalle.',
                    'tipo' => 'error'
                ], 400);
            }

            $monto_grav_5 = 0;
            $monto_grav_10 = 0;
            $monto_iva_5 = 0;
            $monto_iva_10 = 0;
            $monto_exentas = 0;

            foreach ($detalles as $det) {
                $producto = DB::table('productos as p')
                    ->join('tipo_impuestos as ti', 'p.impuesto_id', '=', 'ti.id')
                    ->where('p.id', $det->producto_id)
                    ->select('p.id', 'p.prod_desc', 'ti.id as impuesto_id', 'ti.impuesto_desc')
                    ->first();

                if (!$producto) {
                    return response()->json([
                        'mensaje' => 'Uno de los productos del detalle no existe.',
                        'tipo' => 'error'
                    ], 400);
                }

                $subtotal = $det->compra_cant * $det->compra_costo;

                if ((int)$producto->impuesto_id === 1) {
                    $base10 = $subtotal / 1.10;
                    $iva10 = $subtotal - $base10;

                    $monto_grav_10 += $base10;
                    $monto_iva_10 += $iva10;
                } elseif ((int)$producto->impuesto_id === 2) {
                    $base5 = $subtotal / 1.05;
                    $iva5 = $subtotal - $base5;

                    $monto_grav_5 += $base5;
                    $monto_iva_5 += $iva5;
                } else {
                    $monto_exentas += $subtotal;
                }

                $stock = Stock::where('deposito_id', $nota->deposito_id)
                    ->where('sucursal_id', $nota->sucursal_id)
                    ->where('producto_id', $det->producto_id)
                    ->first();

                if (!$stock) {
                    return response()->json([
                        'mensaje' => 'No existe stock para el producto: ' . $producto->prod_desc,
                        'tipo' => 'error'
                    ], 400);
                }

                if ($nota->nota_comp_tipo === 'NC') {
                    if ($stock->stock_cant_exist < $det->compra_cant) {
                        return response()->json([
                            'mensaje' => 'Stock insuficiente para aplicar la nota de crédito del producto: ' . $producto->prod_desc,
                            'tipo' => 'error'
                        ], 400);
                    }

                    $stock->stock_cant_exist -= $det->compra_cant;
                    $stock->motivo = 'NOTA DE CRÉDITO DE COMPRA';
                } else {
                    $stock->stock_cant_exist += $det->compra_cant;
                    $stock->motivo = 'NOTA DE DÉBITO DE COMPRA';
                }

                $stock->fecha_movimiento = $nota->nota_comp_fec;
                $stock->save();
            }

            $monto_general = $monto_exentas + $monto_grav_5 + $monto_grav_10 + $monto_iva_5 + $monto_iva_10;

            $proveedor = Proveedore::find($nota->proveedor_id);

            if (!$proveedor) {
                return response()->json([
                    'mensaje' => 'Proveedor no encontrado.',
                    'tipo' => 'error'
                ], 400);
            }

            $primerProducto = DB::table('notas_comp_det as ncd')
                ->join('productos as p', 'p.id', '=', 'ncd.producto_id')
                ->join('tipo_impuestos as ti', 'ti.id', '=', 'p.impuesto_id')
                ->where('ncd.nota_comp_id', $nota->id)
                ->select('ti.id as impuesto_id', 'ti.impuesto_desc')
                ->first();

            Libro_compras::create([
                'compra_id' => $nota->compra_id,
                'lib_comp_fecha' => $nota->nota_comp_fec,
                'proveedor_ruc' => $proveedor->proveedor_ruc ?? '',
                'lib_comp_tipo_doc' => $nota->nota_comp_tipo,
                'lib_comp_nro_doc' => $nota->nota_comp_fact,
                'lib_comp_monto' => $monto_general,
                'lib_comp_grav_10' => $monto_grav_10,
                'lib_comp_iva_10' => $monto_iva_10,
                'lib_comp_grav_5' => $monto_grav_5,
                'lib_comp_iva_5' => $monto_iva_5,
                'lib_comp_exentas' => $monto_exentas,
                'proveedor_id' => $proveedor->id,
                'proveedor_desc' => $proveedor->proveedor_desc
            ]);

            if ((int)$compra->tipo_fact_id === 7) {
                $cuentas = Ctas_pagar::where('compra_id', $nota->compra_id)->get();

                if ($cuentas->isEmpty()) {
                    return response()->json([
                        'mensaje' => 'La compra es a crédito, pero no posee cuentas a pagar generadas.',
                        'tipo' => 'error'
                    ], 400);
                }

                if ($nota->nota_comp_tipo === 'NC') {
                    $saldoTotal = $cuentas->sum('saldo');

                    if ($saldoTotal < $monto_general) {
                        return response()->json([
                            'mensaje' => 'El monto de la nota de crédito supera el saldo pendiente de las cuentas a pagar.',
                            'tipo' => 'error'
                        ], 400);
                    }

                    $saldoNota = $monto_general;

                    foreach ($cuentas as $cuenta) {
                        if ($saldoNota <= 0) {
                            break;
                        }

                        $descuento = min($cuenta->saldo, $saldoNota);
                        $cuenta->saldo -= $descuento;
                        $saldoNota -= $descuento;
                        $cuenta->save();
                    }
                } else {
                    $montoPorCuenta = $monto_general / $cuentas->count();

                    foreach ($cuentas as $cuenta) {
                        $cuenta->saldo += $montoPorCuenta;
                        $cuenta->save();
                    }
                }
            }

            $nota->nota_comp_estado = 'CONFIRMADO';
            $nota->user_id = $user->id;
            $nota->save();

            return response()->json([
                'mensaje' => 'Nota confirmada y aplicada correctamente.',
                'tipo' => 'success',
                'registro' => [
                    'nota' => $nota,
                    'monto_exentas' => $monto_exentas,
                    'monto_grav_5' => $monto_grav_5,
                    'monto_grav_10' => $monto_grav_10,
                    'monto_iva_5' => $monto_iva_5,
                    'monto_iva_10' => $monto_iva_10,
                    'monto_general' => $monto_general,
                ]
            ], 200);
        });
    }
}
