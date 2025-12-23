<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cobros_cab;

class Cobros_cabController extends Controller
{
    public function read()
    {
        return DB::select("
            select 
                cc.id,
                e.empresa_desc,
            	s.suc_desc,
            	ca.id as caja_id,
            	ca.caja_desc,
            	ac.id as apertura_cierre_id,
            	'APERTURA #' || ac.id as apertura_cierre_desc,
                cc.cobro_fecha,
                cl.id as cliente_id,
                p.pers_nombre || ' ' || p.pers_apellido as nombre_cliente,
                cc.venta_id,
                'VENTA NRO:' || to_char(vc.id, '0000000') || 
		        ' FECHA: ' || to_char(vc.venta_fec, 'dd/mm/yyyy HH24:mi:ss') || 
		        ' (' || vc.venta_estado || ')' AS venta,
		        'FACTURA: ' || vc.venta_fact AS venta_fact,
				cc.cobro_estado,
				cc.user_id,
                u.name as usuario
            from cobros_cab cc
            join clientes cl on cl.id = cc.cliente_id
            join personas p on p.id = cl.persona_id
            JOIN empresas e ON e.id = cc.empresa_id
            JOIN sucursales s ON s.id = cc.sucursal_id
            join users u on u.id = cc.user_id
            join cajas ca on ca.id = cc.caja_id
            join aperturas_cierres ac on ac.id = cc.apertura_cierre_id
            JOIN ventas_cab vc ON vc.id = cc.venta_id
			order by cc.id desc;
        ");
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'empresa_id'          => 'required',
            'sucursal_id'          => 'required',
            'cliente_id'          => 'required',
            'user_id'             => 'required',
            'caja_id'             => 'required',
            'apertura_cierre_id'  => 'required',
            'venta_id'          => 'required',
            'cobro_fecha'         => 'required',
            'cobro_monto'         => 'nullable',
            'observacion'          => 'nullable'
        ]);

        $datos['cobro_estado'] = 'REGISTRADO';

        DB::beginTransaction();
        try {

            $cobro = Cobros_cab::create($datos);
            $cobro->save();

            DB::commit();

            return response()->json([
                'mensaje' => 'Cobro registrado con éxito',
                'tipo'    => 'success',
                'registro'=> $cobro
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al registrar cobro',
                'tipo'    => 'error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $cobro = Cobros_cab::find($id);

        if (!$cobro) {
            return response()->json([
                'mensaje' => 'Cobro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($cobro->cobro_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden editar cobros REGISTRADOS',
                'tipo'    => 'error'
            ], 400);
        }

        $datos = $request->validate([
            'cobro_fecha' => 'required',
            'cobro_monto' => 'required'
        ]);

        $cobro->update($datos);

        return response()->json([
            'mensaje' => 'Cobro modificado con éxito',
            'tipo'    => 'success',
            'registro'=> $cobro
        ], 200);
    }

    public function anular($id)
    {
        $cobro = Cobros_cab::find($id);

        if (!$cobro) {
            return response()->json([
                'mensaje' => 'Cobro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($cobro->cobro_estado === 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'No se puede anular un cobro CONFIRMADO',
                'tipo'    => 'error'
            ], 400);
        }

        $cobro->update(['cobro_estado' => 'ANULADO']);

        return response()->json([
            'mensaje' => 'Cobro anulado con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    public function confirmar($id)
    {
        $cobro = Cobros_cab::find($id);

        if (!$cobro) {
            return response()->json([
                'mensaje' => 'Cobro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($cobro->cobro_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar cobros REGISTRADOS',
                'tipo'    => 'error'
            ], 400);
        }

        DB::beginTransaction();
        try {

            // Obtener detalles
            $detalles = DB::select("
                select *
                from cobros_det
                where cobro_id = ?
            ", [$id]);

            if (count($detalles) === 0) {
                throw new \Exception("El cobro no tiene detalles cargados");
            }

            //VALIDACIÓN GLOBAL: total cobrado vs saldo total disponible
            $totalCobrado = 0;
            $saldoTotal   = 0;

            foreach ($detalles as $det) {
                $totalCobrado += $det->monto_cobro;

                $cta = DB::selectOne("
                    select ctas_cob_saldo
                    from ctas_cobrar
                    where id = ?
                    and venta_id = ?
                ", [$det->cta_cobrar_id, $det->cta_cobrar_venta_id]);

                if (!$cta) {
                    throw new \Exception("Cuenta a cobrar no encontrada");
                }

                $saldoTotal += $cta->ctas_cob_saldo;
            }

            if ($totalCobrado > $saldoTotal) {
                throw new \Exception("El total cobrado supera el saldo pendiente");
            }

            
            //PROCESO DETALLE POR DETALLE
            foreach ($detalles as $det) {

                // Obtener cuenta a cobrar con bloqueo
                $cta = DB::selectOne("
                    select *
                    from ctas_cobrar
                    where id = ?
                    and venta_id = ?
                    for update
                ", [$det->cta_cobrar_id, $det->cta_cobrar_venta_id]);

                if (!$cta) {
                    throw new \Exception("Cuenta a cobrar no encontrada");
                }

                if ($det->monto_cobro > $cta->ctas_cob_saldo) {
                    throw new \Exception("El monto cobrado excede el saldo pendiente");
                }

                // Actualizar saldo
                $nuevoSaldo = $cta->ctas_cob_saldo - $det->monto_cobro;

                DB::update("
                    update ctas_cobrar
                    set ctas_cob_saldo = ?
                    where id = ?
                    and venta_id = ?
                ", [$nuevoSaldo, $cta->id, $cta->venta_id]);

                // Impactar caja SOLO si es efectivo
                if ($det->forma_cobro_id == 2) { // 2 = EFECTIVO
                    DB::insert("
                        insert into movimientos_caja
                        (
                            apertura_cierre_id,
                            mov_tipo,
                            mov_concepto,
                            mov_monto,
                            mov_origen_tipo,
                            origen_id,
                            created_at
                        )
                        values (?, ?, ?, ?, ?, ?, now())
                    ", [
                        $cobro->apertura_cierre_id,
                        'INGRESO',
                        'COBRO EFECTIVO',
                        $det->monto_cobro,
                        'COBRO',
                        $cobro->id
                    ]);
                }

                // Cheques y tarjetas:
                // ya están registrados en cobros_cheques / cobros_tarjetas
                // NO se impacta caja aquí
            }

            // Confirmar cabecera
            DB::update("
                update cobros_cab
                set cobro_estado = 'CONFIRMADO'
                where id = ?
            ", [$id]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Cobro confirmado con éxito',
                'tipo'    => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al confirmar cobro',
                'tipo'    => 'error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function anularConfirmado($id)
    {
        $cobro = Cobros_cab::find($id);

        if (!$cobro) {
            return response()->json([
                'mensaje' => 'Cobro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($cobro->cobro_estado !== 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden anular cobros CONFIRMADOS',
                'tipo'    => 'error'
            ], 400);
        }

        DB::beginTransaction();
        try {

            //Verificar apertura de caja
            $apertura = DB::selectOne("
                select *
                from aperturas_cierres
                where id = ?
            ", [$cobro->apertura_cierre_id]);

            if (!$apertura || $apertura->estado !== 'ABIERTA') {
                throw new \Exception('No se puede anular un cobro con la caja cerrada');
            }

            //Obtener detalles del cobro
            $detalles = DB::select("
                select *
                from cobros_det
                where cobro_id = ?
            ", [$id]);

            if (count($detalles) === 0) {
                throw new \Exception('El cobro no tiene detalles');
            }

            //Revertir cada detalle
            foreach ($detalles as $det) {

                // Bloquear cuenta a cobrar
                $cta = DB::selectOne("
                    select *
                    from ctas_cobrar
                    where id = ?
                    and venta_id = ?
                    for update
                ", [$det->cta_cobrar_id, $det->cta_cobrar_venta_id]);

                if (!$cta) {
                    throw new \Exception('Cuenta a cobrar no encontrada');
                }

                // Revertir saldo
                DB::update("
                    update ctas_cobrar
                    set ctas_cob_saldo = ctas_cob_saldo + ?
                    where id = ?
                    and venta_id = ?
                ", [
                    $det->monto_cobro,
                    $det->cta_cobrar_id,
                    $det->cta_cobrar_venta_id
                ]);

                // Revertir caja SOLO si fue efectivo 
                if ($det->forma_cobro_id == 2) { // 2 = EFECTIVO

                    // Validar que exista ingreso previo
                    $mov = DB::selectOne("
                        select 1
                        from movimientos_caja
                        where origen_id = ?
                        and mov_origen_tipo = 'COBRO'
                        and mov_tipo = 'INGRESO'
                        limit 1
                    ", [$cobro->id]);

                    if (!$mov) {
                        throw new \Exception('No existe movimiento de ingreso para revertir');
                    }

                    DB::insert("
                        insert into movimientos_caja
                        (
                            apertura_cierre_id,
                            mov_tipo,
                            mov_concepto,
                            mov_monto,
                            mov_origen_tipo,
                            origen_id,
                            created_at
                        )
                        values (?, ?, ?, ?, ?, ?, now())
                    ", [
                        $cobro->apertura_cierre_id,
                        'EGRESO',
                        'ANULACIÓN COBRO EFECTIVO',
                        $det->monto_cobro,
                        'COBRO',
                        $cobro->id
                    ]);
                }

                // Cheques / Tarjetas:
                // NO se eliminan ni se modifican aquí
                // Se controlan luego desde Tesorería
            }

            //Anular cabecera
            DB::update("
                update cobros_cab
                set cobro_estado = 'ANULADO'
                where id = ?
            ", [$id]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Cobro anulado correctamente',
                'tipo'    => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al anular cobro',
                'tipo'    => 'error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function totalChequesApertura(Request $request)
    {
        $request->validate([
            'apertura_cierre_id' => 'required|integer'
        ]);

        $total = DB::selectOne("
            select coalesce(sum(cd.monto_cobro),0) as total
            from cobros_det cd
            join cobros_cab cc on cc.id = cd.cobro_id
            join cobros_cheques ch on ch.cobro_id = cd.cobro_id
            where cc.apertura_cierre_id = ?
            and cc.cobro_estado = 'CONFIRMADO'
            and cd.forma_cobro_id = 3
        ", [$request->apertura_cierre_id]);

        return response()->json([
            'total' => $total->total
        ], 200);
    }

    public function totalTarjetasApertura(Request $request)
    {
        $request->validate([
            'apertura_cierre_id' => 'required|integer'
        ]);

        $total = DB::selectOne("
            select coalesce(sum(cd.monto_cobro),0) as total
            from cobros_det cd
            join cobros_cab cc on cc.id = cd.cobro_id
            join cobros_tarjetas ct on ct.cobro_id = cd.cobro_id
            where cc.apertura_cierre_id = ?
            and cc.cobro_estado = 'CONFIRMADO'
            and cd.forma_cobro_id = 4
        ", [$request->apertura_cierre_id]);

        return response()->json([
            'total' => $total->total
        ], 200);
    }
}
