<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Aperturas_cierres;
use App\Models\Cajas;

class Aperturas_cierresController extends Controller
{
    public function read(Request $request)
    {
        try {

            $userId = $request->user_id;

            if (!$userId) {
                return response()->json([], 200);
            }

            $data = DB::table('aperturas_cierres as ac')
                ->join('cajas as c', 'c.id', '=', 'ac.caja_id')
                ->select(
                    'ac.id',
                    'c.caja_desc',
                    DB::raw("to_char(ac.apertura_fec, 'DD/MM/YYYY HH24:MI') as apertura_fec"),
                    'ac.apertura_monto',
                    DB::raw("to_char(ac.cierre_fec, 'DD/MM/YYYY HH24:MI') as cierre_fec"),
                    'ac.cierre_monto_sistema',
                    'ac.cierre_monto_arqueo',
                    'ac.cierre_diferencia',
                    'ac.estado'
                )
                ->where('ac.user_id', $userId)
                ->orderBy('ac.apertura_fec', 'desc')
                ->get();

            return response()->json($data, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }
    
    public function buscarCajaAbierta()
    {
        try {

            $userId = Auth::id();

            // 1. Buscar caja asignada al usuario
            $caja = Cajas::where('user_id', $userId)->first();

            if (!$caja) {
                return response()->json([
                    'abierta' => false,
                    'mensaje' => 'El usuario no tiene caja asignada'
                ], 200);
            }

            // 2. Buscar apertura ABIERTA para esa caja
            $apertura = DB::table('aperturas_cierres')
                ->where('caja_id', $caja->id)
                ->where('estado', 'ABIERTA')
                ->first();

            if (!$apertura) {
                return response()->json([
                    'abierta' => false
                ], 200);
            }

            // 3. Calcular monto sistema (INGRESOS - EGRESOS)
            $ingresos = DB::table('movimientos_caja')
                ->where('apertura_cierre_id', $apertura->id)
                ->where('mov_tipo', 'INGRESO')
                ->sum('mov_monto');

            $egresos = DB::table('movimientos_caja')
                ->where('apertura_cierre_id', $apertura->id)
                ->where('mov_tipo', 'EGRESO')
                ->sum('mov_monto');

            $montoSistema = $ingresos - $egresos;

            // 4. Retornar datos de la apertura
            return response()->json([
                'abierta' => true,
                'apertura' => [
                    'id' => $apertura->id,
                    'caja_id' => $apertura->caja_id,
                    'apertura_fec' => $apertura->apertura_fec,
                    'apertura_monto' => $apertura->apertura_monto,
                    'estado' => $apertura->estado
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function storeApertura(Request $request)
    {
        try {

            $request->validate([
                'apertura_monto' => 'required|numeric|min:0'
            ]);

            $userId = $request->user_id;
            if (!$userId) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Usuario no identificado'
            ], 400);
}

            // 1. Buscar caja asignada al usuario
            $caja = DB::table('cajas')
                ->where('user_id', $userId)
                ->first();

            if (!$caja) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'El usuario no tiene una caja asignada'
                ], 400);
            }

            // 2. Verificar si ya existe apertura ABIERTA
            $existeApertura = DB::table('aperturas_cierres')
                ->where('caja_id', $caja->id)
                ->where('estado', 'ABIERTA')
                ->exists();

            if ($existeApertura) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Ya existe una apertura de caja activa'
                ], 400);
            }

            // 3. Registrar apertura
            $apertura = Aperturas_cierres::create([
                'caja_id' => $caja->id,
                'user_id' => $userId,
                'apertura_fec' => now(),
                'apertura_monto' => $request->apertura_monto,
                'estado' => 'ABIERTA'
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Caja abierta correctamente',
                'apertura_id' => $apertura->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function cerrarCaja(Request $request)
    {
        try {

            $request->validate([
                'apertura_cierre_id' => 'required|integer',
                'monto_arqueo' => 'required|numeric|min:0'
            ]);

            // 1. Buscar la apertura seleccionada
            $apertura = Aperturas_cierres::find($request->apertura_cierre_id);

            if (!$apertura) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Registro de apertura no encontrado'
                ], 400);
            }

            if ($apertura->estado !== 'ABIERTA') {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'La caja ya fue cerrada'
                ], 400);
            }

            // 2. Calcular monto sistema desde movimientos
            $ingresos = DB::table('movimientos_caja')
                ->where('apertura_cierre_id', $apertura->id)
                ->where('mov_tipo', 'INGRESO')
                ->sum('mov_monto');

            $egresos = DB::table('movimientos_caja')
                ->where('apertura_cierre_id', $apertura->id)
                ->where('mov_tipo', 'EGRESO')
                ->sum('mov_monto');

            $montoSistema = $ingresos - $egresos;

            // 3. Calcular diferencia
            $montoArqueo = $request->monto_arqueo;
            $diferencia = $montoArqueo - $montoSistema;

            // 4. Actualizar cierre
            $apertura->update([
                'cierre_fec' => now(),
                'cierre_monto_sistema' => $montoSistema,
                'cierre_monto_arqueo' => $montoArqueo,
                'cierre_diferencia' => $diferencia,
                'estado' => 'CERRADA'
            ]);

            return response()->json([
                'error' => false,
                'mensaje' => 'Caja cerrada correctamente',
                'resumen' => [
                    'monto_sistema' => $montoSistema,
                    'monto_arqueo' => $montoArqueo,
                    'diferencia' => $diferencia
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

}
