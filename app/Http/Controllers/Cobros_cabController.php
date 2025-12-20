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
                cc.cobro_fecha,
                cc.cobro_estado,
                cc.cobro_monto,

                cl.id as cliente_id,
                p.pers_nombre || ' ' || p.pers_apellido as cliente_nombre,

                u.name as usuario,
                ca.id as caja_id,
                ac.id as apertura_cierre_id

            from cobros_cab cc
            join clientes cl on cl.id = cc.cliente_id
            join personas p on p.id = cl.persona_id
            join users u on u.id = cc.user_id
            join cajas ca on ca.id = cc.caja_id
            join aperturas_cierres ac on ac.id = cc.apertura_cierre_id
			order by cc.id desc
        ");
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cliente_id'          => 'required',
            'user_id'             => 'required',
            'caja_id'             => 'required',
            'apertura_cierre_id'  => 'required',
            'cobro_fecha'         => 'required',
            'cobro_monto'         => 'required'
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
            //Obtener detalles
            $detalles = DB::select("
                select *
                from cobros_det
                where cobro_id = ?
            ", [$id]);

            if (count($detalles) === 0) {
                throw new \Exception("El cobro no tiene detalles cargados");
            }

            //Procesar cada detalle
            foreach ($detalles as $det) {

            // Obtener cuenta a cobrar 
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

            // Actualizar saldo de la cuenta a cobrar
            $nuevoSaldo = $cta->ctas_cob_saldo - $det->monto_cobro;

            DB::update("
                update ctas_cobrar
                set ctas_cob_saldo = ?
                where id = ?
                  and venta_id = ?
            ", [$nuevoSaldo, $cta->id, $cta->venta_id]);

            //Impactar caja (solo efectivo)
            if ($det->forma_cobro_id == 2) { // 2 = EFECTIVO
                DB::insert("
                    insert into movimientos_caja
                    (apertura_cierre_id,
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
                $det->monto,
                'COBRO',
                $cobro->id
            ]);
        }

            //Cheques / Tarjetas (ya están registrados en cobros_cheques / cobros_tarjetas) No se impacta nada acá
        }

        //Confirmar cobro
        DB::update("
            update cobros_cab
            set cobro_estado = 'CONFIRMADO'
            where id = ?
        ", [$id]);

        DB::commit();

            return response()->json([
                'mensaje' => 'Cobro confirmado con éxito',
                'tipo' => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al confirmar cobro',
                'tipo' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function anularConfirmado($id)
{
    $cobro = Cobros_cab::find($id);

    if (!$cobro) {
        return response()->json([
            'mensaje' => 'Cobro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    if ($cobro->cobro_estado !== 'CONFIRMADO') {
        return response()->json([
            'mensaje' => 'Solo se pueden anular cobros CONFIRMADOS',
            'tipo' => 'error'
        ], 400);
    }

    DB::beginTransaction();
    try {

        //Verificar apertura
        $apertura = DB::selectOne("
            select *
            from aperturas_cierres
            where id = ?
        ", [$cobro->apertura_cierre_id]);

        if (!$apertura || $apertura->estado !== 'ABIERTA') {
            throw new \Exception('No se puede anular un cobro con la caja cerrada');
        }

        //Obtener detalles
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

            /* 3.1 Revertir ctas_cobrar */
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

            /* 3.2 Revertir caja (solo efectivo) */
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
                    'EGRESO',
                    'ANULACION COBRO EFECTIVO',
                    $det->monto_cobro,
                    'COBRO',
                    $cobro->id
                ]);
            }

            //Cheques / Tarjetas (no se eliminan se dejan para control en Tesorería (más adelante se puede validar estados))
        }

        // Anular cabecera
        DB::update("
            update cobros_cab
            set cobro_estado = 'ANULADO'
            where id = ?
        ", [$id]);

        DB::commit();

        return response()->json([
            'mensaje' => 'Cobro anulado correctamente',
            'tipo' => 'success'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'mensaje' => 'Error al anular cobro',
            'tipo' => 'error',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function buscar(Request $r)
    {
        return DB::select("
            select 
                cc.id,
                cc.cobro_fecha,
                cc.cobro_estado,
                cc.cobro_monto,
                p.pers_nombre || ' ' || p.pers_apellido as cliente_nombre
            from cobros_cab cc
            join clientes cl on cl.id = cc.cliente_id
            join personas p on p.id = cl.persona_id
            where p.pers_nombre ilike '%$r->cliente_nombre%'
            order by cc.id desc
        ");
    }

}
