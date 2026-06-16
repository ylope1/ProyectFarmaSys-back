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
use App\Traits\VerificaPermisos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Compras_cabController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'movimientos/compras/compras/';

    public function read()
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

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
                'ORDEN NRO: ' || LPAD(oc.id::text, 7, '0') ||
                ' - CUOTAS: ' || COALESCE(cc.compra_cant_cta::text, '1') ||
                ' - IFV: ' || COALESCE(cc.compra_ifv::text, '0') ||
                ' - FECHA APROB: ' || to_char(oc.orden_comp_fec_aprob, 'dd/mm/yyyy') ||
                ' - ESTADO ORDEN: ' || oc.orden_comp_estado as orden
            FROM compras_cab cc
            JOIN proveedores p ON p.id = cc.proveedor_id
            JOIN empresas e ON e.id = cc.empresa_id
            JOIN sucursales s ON s.id = cc.sucursal_id
            JOIN depositos d ON d.id = cc.deposito_id
            JOIN users u ON u.id = cc.user_id
            JOIN tipo_fact tf ON tf.id = cc.tipo_fact_id
            JOIN orden_comp_cab oc ON oc.id = cc.orden_comp_id
            ORDER BY cc.id DESC
        ");
    }

    public function store(Request $request)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $user = auth()->user();

        $funcionario = $this->obtenerFuncionarioPorUsuario($user->id);

        if (!$funcionario) {
            return response()->json([
                'mensaje' => 'El usuario autenticado no está vinculado a un funcionario',
                'tipo' => 'error'
            ], 400);
        }

        if ($request->compra_cant_cta === '') {
            $request->merge(['compra_cant_cta' => null]);
        }

        $datosValidados = $request->validate([
            'orden_comp_id'   => 'required|exists:orden_comp_cab,id',
            'deposito_id'     => 'required|exists:depositos,id',
            'compra_fact'     => 'required|string|max:30',
            'compra_timbrado' => 'required|string|max:30',
            'compra_fec'      => 'required',
            'compra_cant_cta' => 'nullable|integer|min:1',
        ]);

        $depositoValido = DB::table('depositos')
            ->where('id', $datosValidados['deposito_id'])
            ->where('sucursal_id', $funcionario->sucursal_id)
            ->exists();

        if (!$depositoValido) {
            return response()->json([
                'mensaje' => 'El depósito seleccionado no corresponde a la sucursal del usuario',
                'tipo' => 'error'
            ], 400);
        }

        $orden = DB::table('orden_comp_cab')
            ->where('id', $datosValidados['orden_comp_id'])
            ->first();

        if (!$orden) {
            return response()->json([
                'mensaje' => 'Orden de compra no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        if ($orden->orden_comp_estado !== 'APROBADO') {
            return response()->json([
                'mensaje' => 'Solo se puede registrar una compra desde una orden de compra APROBADA',
                'tipo' => 'error'
            ], 400);
        }

        $detallesOrden = DB::table('orden_comp_det')
            ->where('orden_comp_id', $orden->id)
            ->get();

        if ($detallesOrden->isEmpty()) {
            return response()->json([
                'mensaje' => 'La orden de compra no tiene detalle',
                'tipo' => 'error'
            ], 400);
        }

        $facturaDuplicada = DB::table('compras_cab')
            ->where('proveedor_id', $orden->proveedor_id)
            ->where('compra_timbrado', $datosValidados['compra_timbrado'])
            ->where('compra_fact', $datosValidados['compra_fact'])
            ->where('compra_estado', '<>', 'ANULADO')
            ->exists();

        if ($facturaDuplicada) {
            return response()->json([
                'mensaje' => 'Ya existe una compra registrada con el mismo proveedor, timbrado y número de factura',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $tipoFactId = $orden->tipo_fact_id;
            $compraIfv = $orden->orden_comp_ifv ?? 0;

            if ((int)$tipoFactId === 6) {
                $compraCantCta = 1;
                $compraIfv = 0;
            } else {
                $compraCantCta = $datosValidados['compra_cant_cta'] ?? null;

                if (!$compraCantCta || $compraCantCta <= 0) {
                    DB::rollBack();

                    return response()->json([
                        'mensaje' => 'Debe ingresar la cantidad de cuotas para compras a crédito',
                        'tipo' => 'error'
                    ], 400);
                }
            }

            $compraId = DB::table('compras_cab')->insertGetId([
                'orden_comp_id'     => $orden->id,
                'proveedor_id'      => $orden->proveedor_id,
                'user_id'           => $user->id,
                'sucursal_id'       => $funcionario->sucursal_id,
                'empresa_id'        => $funcionario->empresa_id,
                'deposito_id'       => $datosValidados['deposito_id'],
                'tipo_fact_id'      => $tipoFactId,
                'compra_fact'       => $datosValidados['compra_fact'],
                'compra_timbrado'   => $datosValidados['compra_timbrado'],
                'compra_fec'        => $datosValidados['compra_fec'],
                'compra_fec_recep'  => null,
                'compra_cant_cta'   => $compraCantCta,
                'compra_ifv'        => $compraIfv,
                'compra_estado'     => 'PENDIENTE'
            ]);

            foreach ($detallesOrden as $det) {
                DB::table('compras_det')->insert([
                    'compra_id'    => $compraId,
                    'producto_id'  => $det->producto_id,
                    'compra_cant'  => $det->orden_comp_cant,
                    'compra_costo' => $det->orden_comp_costo
                ]);
            }

            DB::table('orden_comp_cab')
                ->where('id', $orden->id)
                ->update([
                    'orden_comp_estado' => 'PROCESADO'
                ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Compra registrada con éxito',
                'tipo' => 'success',
                'registro' => DB::table('compras_cab')->where('id', $compraId)->first()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al registrar la compra: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $user = auth()->user();

        $funcionario = $this->obtenerFuncionarioPorUsuario($user->id);

        if (!$funcionario) {
            return response()->json([
                'mensaje' => 'El usuario autenticado no está vinculado a un funcionario',
                'tipo' => 'error'
            ], 400);
        }

        if ($request->compra_cant_cta === '') {
            $request->merge(['compra_cant_cta' => null]);
        }
        
        $compra = DB::table('compras_cab')
            ->where('id', $id)
            ->first();

        if (!$compra) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($compra->compra_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden modificar compras en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        $datosValidados = $request->validate([
            'deposito_id'     => 'required|exists:depositos,id',
            'compra_fact'     => 'required|string|max:30',
            'compra_timbrado' => 'required|string|max:30',
            'compra_fec'      => 'required',
            'compra_cant_cta' => 'nullable|integer|min:1',
        ]);

        $depositoValido = DB::table('depositos')
            ->where('id', $datosValidados['deposito_id'])
            ->where('sucursal_id', $funcionario->sucursal_id)
            ->exists();

        if (!$depositoValido) {
            return response()->json([
                'mensaje' => 'El depósito seleccionado no corresponde a la sucursal del usuario',
                'tipo' => 'error'
            ], 400);
        }

        $facturaDuplicada = DB::table('compras_cab')
            ->where('proveedor_id', $compra->proveedor_id)
            ->where('compra_timbrado', $datosValidados['compra_timbrado'])
            ->where('compra_fact', $datosValidados['compra_fact'])
            ->where('id', '<>', $id)
            ->where('compra_estado', '<>', 'ANULADO')
            ->exists();

        if ($facturaDuplicada) {
            return response()->json([
                'mensaje' => 'Ya existe otra compra registrada con el mismo proveedor, timbrado y número de factura',
                'tipo' => 'error'
            ], 400);
        }

        $cantidadCuotas = $compra->compra_cant_cta;

        if ((int)$compra->tipo_fact_id === 6) {
            $cantidadCuotas = 1;
        } else {
            $cantidadCuotas = $datosValidados['compra_cant_cta'] ?? null;

            if (!$cantidadCuotas || $cantidadCuotas <= 0) {
                return response()->json([
                    'mensaje' => 'Debe ingresar la cantidad de cuotas para compras a crédito',
                    'tipo' => 'error'
                ], 400);
            }
        }

        DB::table('compras_cab')
            ->where('id', $id)
            ->update([
                'deposito_id'      => $datosValidados['deposito_id'],
                'compra_fact'      => $datosValidados['compra_fact'],
                'compra_timbrado'  => $datosValidados['compra_timbrado'],
                'compra_fec'       => $datosValidados['compra_fec'],
                'compra_cant_cta'  => $cantidadCuotas
            ]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => DB::table('compras_cab')->where('id', $id)->first()
        ], 200);
    }

    public function anular($id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular');
        if ($permiso) {
            return $permiso;
        }

        $user = auth()->user();

        $compra = DB::table('compras_cab')->where('id', $id)->first();

        if (!$compra) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($compra->compra_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden anular compras en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        DB::beginTransaction();

        try {
            DB::table('compras_cab')
                ->where('id', $id)
                ->update([
                    'compra_estado' => 'ANULADO',
                    'user_id' => $user->id
                ]);

            if ($compra->orden_comp_id) {
                DB::table('orden_comp_cab')
                    ->where('id', $compra->orden_comp_id)
                    ->update([
                        'orden_comp_estado' => 'APROBADO'
                    ]);
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Compra anulada con éxito',
                'tipo' => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al anular la compra: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }

    public function confirmar($id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'confirmar');
        if ($permiso) {
            return $permiso;
        }

        $user = auth()->user();

        $compra = DB::table('compras_cab')
            ->where('id', $id)
            ->first();

        if (!$compra) {
            return response()->json([
                'mensaje' => 'Compra no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        if ($compra->compra_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar compras en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        $detalles = DB::select("
            SELECT 
                cd.producto_id,
                cd.compra_cant,
                cd.compra_costo,
                p.impuesto_id
            FROM compras_det cd
            JOIN productos p ON p.id = cd.producto_id
            WHERE cd.compra_id = ?
        ", [$id]);

        if (empty($detalles)) {
            return response()->json([
                'mensaje' => 'La compra no puede confirmarse sin detalle',
                'tipo' => 'error'
            ], 400);
        }

        $proveedor = DB::table('proveedores')
            ->where('id', $compra->proveedor_id)
            ->first();

        if (!$proveedor) {
            return response()->json([
                'mensaje' => 'Proveedor no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $tipoFactura = DB::table('tipo_fact')
            ->where('id', $compra->tipo_fact_id)
            ->first();

        if (!$tipoFactura) {
            return response()->json([
                'mensaje' => 'Tipo de factura no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $totales = $this->calcularTotalesCompra($id);

        DB::beginTransaction();

        try {
            DB::table('compras_cab')
                ->where('id', $id)
                ->update([
                    'compra_estado' => 'RECIBIDO',
                    'compra_fec_recep' => now(),
                    'user_id' => $user->id
                ]);

            foreach ($detalles as $det) {
                $stock = DB::table('stock')
                    ->where('deposito_id', $compra->deposito_id)
                    ->where('sucursal_id', $compra->sucursal_id)
                    ->where('producto_id', $det->producto_id)
                    ->first();

                if ($stock) {
                    DB::table('stock')
                        ->where('deposito_id', $compra->deposito_id)
                        ->where('sucursal_id', $compra->sucursal_id)
                        ->where('producto_id', $det->producto_id)
                        ->update([
                            'stock_cant_exist' => DB::raw('stock_cant_exist + ' . $det->compra_cant),
                            'fecha_movimiento' => now(),
                            'motivo' => 'COMPRA NRO: ' . $id
                        ]);
                } else {
                    DB::table('stock')->insert([
                        'deposito_id' => $compra->deposito_id,
                        'sucursal_id' => $compra->sucursal_id,
                        'producto_id' => $det->producto_id,
                        'stock_cant_exist' => $det->compra_cant,
                        'stock_cant_min' => 0,
                        'stock_cant_max' => 0,
                        'cantidad_exceso' => 0,
                        'fecha_movimiento' => now(),
                        'motivo' => 'COMPRA NRO: ' . $id
                    ]);
                }

                DB::table('productos')
                    ->where('id', $det->producto_id)
                    ->update([
                    'prod_precio_comp' => $det->compra_costo
                ]);
            }

            DB::table('ctas_pagar')
                ->where('compra_id', $id)
                ->delete();

            $cantidadCuotas = (int)($compra->compra_cant_cta ?? 1);

            if ($cantidadCuotas <= 0) {
                $cantidadCuotas = 1;
            }

            if ((int)$compra->tipo_fact_id === 6) {
                $cantidadCuotas = 1;
            }

            $intervalo = (int)($compra->compra_ifv ?? 0);
            $montoTotal = $totales['monto_general'];
            $montoCuota = round($montoTotal / $cantidadCuotas);

            for ($i = 1; $i <= $cantidadCuotas; $i++) {
                $saldoCuota = $montoCuota;

                if ($i == $cantidadCuotas) {
                    $saldoCuota = $montoTotal - ($montoCuota * ($cantidadCuotas - 1));
                }

                DB::table('ctas_pagar')->insert([
                    'id' => $i,
                    'compra_id' => $id,
                    'monto' => $saldoCuota,
                    'saldo' => $saldoCuota,
                    'fecha_vencimiento' => \Carbon\Carbon::parse($compra->compra_fec)->addDays($intervalo * $i),
                    'nro_cuota' => $i,
                    'estado' => 'PENDIENTE',
                    'tipo_fact_id' => $compra->tipo_fact_id
                ]);
            }

            DB::table('libro_compras')->insert([
                'compra_id' => $id,
                'lib_comp_fecha' => $compra->compra_fec,
                'proveedor_ruc' => $proveedor->proveedor_ruc,
                'lib_comp_tipo_doc' => $tipoFactura->tipo_fact_desc,
                'lib_comp_nro_doc' => $compra->compra_fact,
                'lib_comp_monto' => $totales['monto_general'],
                'lib_comp_grav_10' => $totales['monto_grav_10'],
                'lib_comp_iva_10' => $totales['monto_iva_10'],
                'lib_comp_grav_5' => $totales['monto_grav_5'],
                'lib_comp_iva_5' => $totales['monto_iva_5'],
                'lib_comp_exentas' => $totales['monto_exentas'],
                'proveedor_id' => $proveedor->id,
                'proveedor_desc' => $proveedor->proveedor_desc
            ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Compra confirmada con éxito',
                'tipo' => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al confirmar la compra: ' . $e->getMessage(),
                'tipo' => 'error'
            ], 500);
        }
    }
    public function buscar(Request $r)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'mensaje' => 'Usuario no autenticado.',
                'tipo' => 'error'
            ], 401);
        }

        $buscar = $r->buscar ?? '';

        return DB::select("SELECT 
                cc.id,
                cc.id AS compra_id,
                to_char(cc.compra_fec, 'dd/mm/yyyy HH24:mi:ss') AS compra_fec,
                cc.compra_estado,
                cc.compra_fact,
                cc.compra_timbrado,
                cc.tipo_fact_id,
                tf.tipo_fact_desc,
                cc.empresa_id,  
                e.empresa_desc,
                cc.sucursal_id, 
                s.suc_desc,
                cc.deposito_id,
                d.deposito_desc,
                cc.proveedor_id,
                p.proveedor_desc,
                p.proveedor_ruc,
                cc.user_id, 
                u.name AS encargado,
                'COMPRA NRO: ' || to_char(cc.id, '0000000') || 
                ' - FACTURA: ' || cc.compra_fact ||
                ' - PROVEEDOR: ' || p.proveedor_desc ||
                ' - FECHA: ' || to_char(cc.compra_fec, 'dd/mm/yyyy HH24:mi:ss') || 
                ' - ESTADO: ' || cc.compra_estado AS compra
            FROM compras_cab cc 
            JOIN empresas e ON e.id = cc.empresa_id
            JOIN sucursales s ON s.id = cc.sucursal_id 
            JOIN depositos d ON d.id = cc.deposito_id
            JOIN proveedores p ON p.id = cc.proveedor_id
            JOIN users u ON u.id = cc.user_id
            JOIN tipo_fact tf ON tf.id = cc.tipo_fact_id
            WHERE cc.compra_estado = 'RECIBIDO'
            AND (
                CAST(cc.id AS TEXT) ILIKE ?
                OR cc.compra_fact ILIKE ?
                OR p.proveedor_desc ILIKE ?
                OR p.proveedor_ruc ILIKE ?
                OR u.name ILIKE ?
            )
            ORDER BY cc.id DESC
        ", [
            '%' . $buscar . '%',
            '%' . $buscar . '%',
            '%' . $buscar . '%',
            '%' . $buscar . '%',
            '%' . $buscar . '%'
        ]);
    }

    private function obtenerFuncionarioPorUsuario($userId) //este vamos a trabajar desde el front nomas
    {
        return DB::table('funcionarios')
            ->where('user_id', $userId)
            ->first();
    }

    private function calcularTotalesCompra($compraId)
    {
        $detalles = DB::select("
            SELECT 
                cd.producto_id,
                cd.compra_cant,
                cd.compra_costo,
                p.impuesto_id
            FROM compras_det cd
            JOIN productos p ON p.id = cd.producto_id
            WHERE cd.compra_id = ?
        ", [$compraId]);

        $monto_exentas = 0;
        $monto_grav_5 = 0;
        $monto_grav_10 = 0;
        $monto_iva_5 = 0;
        $monto_iva_10 = 0;

        foreach ($detalles as $det) {
            $subtotal = $det->compra_cant * $det->compra_costo;

            if ((int)$det->impuesto_id === 1) {
                $monto_grav_10 += $subtotal;
                $monto_iva_10 += $subtotal - ($subtotal / 1.10);
            } elseif ((int)$det->impuesto_id === 2) {
                $monto_grav_5 += $subtotal;
                $monto_iva_5 += $subtotal - ($subtotal / 1.05);
            } else {
                $monto_exentas += $subtotal;
            }
        }

        $monto_general = $monto_exentas + $monto_grav_5 + $monto_grav_10;

        return [
            'monto_exentas' => round($monto_exentas),
            'monto_grav_5' => round($monto_grav_5),
            'monto_grav_10' => round($monto_grav_10),
            'monto_iva_5' => round($monto_iva_5),
            'monto_iva_10' => round($monto_iva_10),
            'monto_general' => round($monto_general)
        ];
    }
}
