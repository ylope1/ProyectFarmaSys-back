<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cobros_cheques;
use App\Models\Cobros_cab;

class Cobros_chequesController extends Controller
{
    public function read($cobro_id)
    {
        return DB::select("
            select 
                cc.*,
                ee.ent_emi_desc as banco
                from cobros_cheques cc
                join entidades_emisoras ee 
                on ee.id = cc.entidad_emisora_id
            where cc.cobro_id = ?
        ", [$cobro_id]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cobro_id'            => 'required',
            'cta_cobrar_id'       => 'required',
            'cta_cobrar_venta_id' => 'required',
            'entidad_emisora_id'  => 'required',
            'nro_cheque'          => 'required',
            'fecha_vto'           => 'required'
        ]);

        /* Validar estado del cobro */
        $cobros_cab = Cobros_cab::find($datos['cobro_id']);
        if (!$cobros_cab || $cobros_cab->cobro_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden cargar cheques en cobros REGISTRADOS',
                'tipo'    => 'error'
            ], 400);
        }

        $datos['estado_cheque'] = 'REGISTRADO';

        DB::beginTransaction();
        try {

            $cheque = Cobros_cheques::create($datos);
            $cheque->save();

            DB::commit();

            return response()->json([
                'mensaje' => 'Cheque registrado con éxito',
                'tipo'    => 'success',
                'registro'=> $cheque
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al registrar cheque',
                'tipo'    => 'error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $datos = $request->validate([
            'cobro_id'            => 'required',
            'cta_cobrar_id'       => 'required',
            'cta_cobrar_venta_id' => 'required',
            'nro_cheque'          => 'required'
        ]);

        /* Validar estado del cobro */
        $cobro_cab = Cobros_cab::find($datos['cobro_id']);
        if (!$cobro_cab || $cobro_cab->cobro_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'No se pueden eliminar cheques de cobros confirmados',
                'tipo'    => 'error'
            ], 400);
        }

        DB::table('cobros_cheques')
            ->where('cobro_id', $datos['cobro_id'])
            ->where('cta_cobrar_id', $datos['cta_cobrar_id'])
            ->where('cta_cobrar_venta_id', $datos['cta_cobrar_venta_id'])
            ->where('nro_cheque', $datos['nro_cheque'])
            ->delete();

        return response()->json([
            'mensaje' => 'Cheque eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }
}
