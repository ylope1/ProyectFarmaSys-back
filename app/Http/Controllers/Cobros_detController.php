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
                cd.forma_cobro_id,
                fc.forma_cob_desc,
                cd.monto_cobro
            from cobros_det cd
            join forma_cobros fc on fc.id = cd.forma_cobro_id
            where cd.cobro_id = ?
            order by cd.forma_cobro_id
        ", [$cobro_id]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cobro_id'               => 'required',
            'cta_cobrar_id'          => 'required',
            'cta_cobrar_venta_id'    => 'required',
            'forma_cobro_id'         => 'required',
            'monto_cobro'            => 'required|numeric|min:1'
        ]);

        /* Validar estado de la cabecera */
        $cobros_cab = Cobros_cab::find($datos['cobro_id']);
        if (!$cobros_cab || $cobros_cab->cobro_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden cargar detalles en cobros REGISTRADOS',
                'tipo'    => 'error'
            ], 400);
        }

        DB::beginTransaction();
        try {

            /* Insertar detalle */
            DB::table('cobros_det')->insert([
                'cobro_id'              => $datos['cobro_id'],
                'cta_cobrar_id'         => $datos['cta_cobrar_id'],
                'cta_cobrar_venta_id'   => $datos['cta_cobrar_venta_id'],
                'forma_cobro_id'        => $datos['forma_cobro_id'],
                'monto_cobro'                 => $datos['monto_cobro']
            ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Detalle de cobro registrado con éxito',
                'tipo'    => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al registrar detalle de cobro',
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

    public function buscar(Request $r)
    {
        return DB::select("
            select 
                cc.id,
                cc.venta_id,
                cc.ctas_cob_saldo,
                v.venta_fact,
                p.pers_nombre || ' ' || p.pers_apellido as cliente_nombre
            from ctas_cobrar cc
            join ventas_cab v on v.id = cc.venta_id
            join clientes cl on cl.id = v.cliente_id
            join personas p on p.id = cl.persona_id
            where cc.ctas_cob_saldo > 0
              and p.pers_nombre ilike '%$r->cliente_nombre%'
            order by cc.id
        ");
    }
}
