<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cobros_det;
use App\Models\Cobros_cab;

class Cobros_detController extends Controller
{
    public function read($cobro_id)
    {
        return DB::select("
            select 
                cd.cobro_id,
                cd.cta_cobrar_id,
                cd.cta_cobrar_venta_id,

                -- Documento
                v.venta_fact as documento,

                -- Cliente
                p.pers_nombre || ' ' || p.pers_apellido as cliente,

                -- Forma de cobro
                cd.forma_cobro_id,
                fc.forma_cob_desc as forma_cobro_desc,

                -- Monto
                cd.monto_cobro as monto
            from cobros_det cd
            join forma_cobros fc 
                on fc.id = cd.forma_cobro_id
            join ventas_cab v
                on v.id = cd.cta_cobrar_venta_id
            join clientes cl
                on cl.id = v.cliente_id
            join personas p
                on p.id = cl.persona_id
            where cd.cobro_id = ?
            order by cd.forma_cobro_id
        ", [$cobro_id]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cobro_id'               => 'required|integer',
            'cta_cobrar_id'          => 'required|integer',
            'cta_cobrar_venta_id'    => 'required|integer',
            'forma_cobro_id'         => 'required|integer',
            'monto_cobro'            => 'required|numeric|min:1',

            // Datos opcionales según forma de cobro
            'cheque.entidad_emisora_id' => 'nullable|integer',
            'cheque.nro_cheque'         => 'nullable|string|max:50',
            'cheque.fecha_vto'          => 'nullable|date',

            'tarjeta.entidad_adherida_tarjeta_id' => 'nullable|integer',
            'tarjeta.nro_tarjeta'                 => 'nullable|string|max:50',
            'tarjeta.fecha_vto'                   => 'nullable|date',
        ]);

        /* Validar estado de la cabecera */
        $cobro = Cobros_cab::find($datos['cobro_id']);
        if (!$cobro || $cobro->cobro_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden cargar detalles en cobros REGISTRADOS',
                'tipo'    => 'error'
            ], 400);
        }

        DB::beginTransaction();
        try {

            /* 1️⃣ Insertar detalle del cobro */
            DB::table('cobros_det')->insert([
                'cobro_id'            => $datos['cobro_id'],
                'cta_cobrar_id'       => $datos['cta_cobrar_id'],
                'cta_cobrar_venta_id' => $datos['cta_cobrar_venta_id'],
                'forma_cobro_id'      => $datos['forma_cobro_id'],
                'monto_cobro'         => $datos['monto_cobro']
            ]);

            /* 2️⃣ Procesar forma de cobro */
            switch ((int)$datos['forma_cobro_id']) {

                /* CHEQUE */
                case 3:
                    if (
                        empty($datos['cheque']['entidad_emisora_id']) ||
                        empty($datos['cheque']['nro_cheque']) ||
                        empty($datos['cheque']['fecha_vto'])
                    ) {
                        throw new \Exception('Datos de cheque incompletos');
                    }

                    DB::table('cobros_cheques')->insert([
                        'cobro_id'              => $datos['cobro_id'],
                        'cta_cobrar_id'         => $datos['cta_cobrar_id'],
                        'cta_cobrar_venta_id'   => $datos['cta_cobrar_venta_id'],
                        'entidad_emisora_id'    => $datos['cheque']['entidad_emisora_id'],
                        'nro_cheque'            => $datos['cheque']['nro_cheque'],
                        'fecha_vto'             => $datos['cheque']['fecha_vto'],
                        'estado_cheque'         => 'REGISTRADO'
                    ]);
                    break;

                /* TARJETA */
                case 4:
                    if (
                        empty($datos['tarjeta']['entidad_adherida_tarjeta_id']) ||
                        empty($datos['tarjeta']['nro_tarjeta']) ||
                        empty($datos['tarjeta']['fecha_vto'])
                    ) {
                        throw new \Exception('Datos de tarjeta incompletos');
                    }

                    DB::table('cobros_tarjetas')->insert([
                        'cobro_id'                       => $datos['cobro_id'],
                        'cta_cobrar_id'                  => $datos['cta_cobrar_id'],
                        'cta_cobrar_venta_id'            => $datos['cta_cobrar_venta_id'],
                        'entidad_adherida_tarjeta_id'    => $datos['tarjeta']['entidad_adherida_tarjeta_id'],
                        'nro_tarjeta'                    => $datos['tarjeta']['nro_tarjeta'],
                        'fecha_vto'                      => $datos['tarjeta']['fecha_vto'],
                        'estado_tarjeta'                 => 'REGISTRADO'
                    ]);
                    break;

                /* EFECTIVO */
                case 2:
                default:
                    // No requiere tabla especializada
                    break;
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Detalle de cobro registrado correctamente',
                'tipo'    => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al registrar el detalle del cobro',
                'tipo'    => 'error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    public function destroy(Request $request)
    {
        $datos = $request->validate([
            'cobro_id'               => 'required',
            'cta_cobrar_id'          => 'required',
            'cta_cobrar_venta_id'    => 'required'
        ]);

        /* Validar estado del cobro */
        $cobros_cab = Cobros_cab::find($datos['cobro_id']);
        if (!$cobros_cab || $cobros_cab->cobro_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden eliminar detalles de cobros REGISTRADOS',
                'tipo'    => 'error'
            ], 400);
        }

        DB::table('cobros_det')
            ->where('cobro_id', $datos['cobro_id'])
            ->where('cta_cobrar_id', $datos['cta_cobrar_id'])
            ->where('cta_cobrar_venta_id', $datos['cta_cobrar_venta_id'])
            ->delete();

        return response()->json([
            'mensaje' => 'Detalle eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    public function buscarCtaCobro(Request $r)
    {
        return DB::select("
            select 
                cc.id,
                cc.venta_id,
                cc.ctas_cob_saldo,
                v.venta_fact,
                p.pers_nombre || ' ' || p.pers_apellido as nombre_cliente
            from ctas_cobrar cc
            join ventas_cab v on v.id = cc.venta_id
            join clientes cl on cl.id = v.cliente_id
            join personas p on p.id = cl.persona_id
            where cc.ctas_cob_saldo > 0
              and p.pers_nombre ilike '%$r->nombre_cliente%'
            order by cc.id
        ");
    }
}
