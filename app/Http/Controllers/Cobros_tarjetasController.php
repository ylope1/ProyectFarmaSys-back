<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cobros_tarjetas;
use App\Models\Cobros_cab;

class Cobros_tarjetasController extends Controller
{
    public function read($cobro_id)
    {
        return DB::select("
            select 
                ct.*,
                eat.id as entidad_adherida_tarjeta_id
            from cobros_tarjetas ct
            join entidades_adheridas_tarjetas eat 
                on eat.id = ct.entidad_adherida_tarjeta_id
            where ct.cobro_id = ?
        ", [$cobro_id]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cobro_id'                    => 'required',
            'cta_cobrar_id'               => 'required',
            'cta_cobrar_venta_id'         => 'required',
            'entidad_adherida_tarjeta_id' => 'required',
            'nro_tarjeta'                 => 'required',
            'fecha_vto'                   => 'required'
        ]);

        /* Validar estado del cobro */
        $cobros_cab = Cobros_cab::find($datos['cobro_id']);
        if (!$cobros_cab || $cobros_cab->cobro_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden cargar tarjetas en cobros REGISTRADOS',
                'tipo'    => 'error'
            ], 400);
        }

        $datos['estado_tarjeta'] = 'REGISTRADO';

        DB::beginTransaction();
        try {

            $tarjeta = Cobros_tarjetas::create($datos);
            $tarjeta->save();

            DB::commit();

            return response()->json([
                'mensaje' => 'Tarjeta registrada con éxito',
                'tipo'    => 'success',
                'registro'=> $tarjeta
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al registrar tarjeta',
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
            'nro_tarjeta'         => 'required'
        ]);

        /* Validar estado del cobro */
        $cobros_cab = Cobros_cab::find($datos['cobro_id']);
        if (!$cobros_cab || $cobros_cab->cobro_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'No se pueden eliminar tarjetas de cobros confirmados',
                'tipo'    => 'error'
            ], 400);
        }

        DB::table('cobros_tarjetas')
            ->where('cobro_id', $datos['cobro_id'])
            ->where('cta_cobrar_id', $datos['cta_cobrar_id'])
            ->where('cta_cobrar_venta_id', $datos['cta_cobrar_venta_id'])
            ->where('nro_tarjeta', $datos['nro_tarjeta'])
            ->delete();

        return response()->json([
            'mensaje' => 'Tarjeta eliminada con éxito',
            'tipo'    => 'success'
        ], 200);
    }

}
