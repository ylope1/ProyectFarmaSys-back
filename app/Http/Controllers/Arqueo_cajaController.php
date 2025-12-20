<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Arqueo_caja;
use App\Models\Aperturas_cierres;

class Arqueo_cajaController extends Controller
{
    //funcion read
    public function read(Request $request)
    {
        try {

             $userId = $request->user_id;

            if (!$userId) {
                return response()->json([], 200);
            }

            $data = DB::table('arqueo_caja as a')
                ->join('aperturas_cierres as ac', 'ac.id', '=', 'a.apertura_cierre_id')
                ->join('cajas as c', 'c.id', '=', 'ac.caja_id')
                ->select(
                    'a.id',
                    DB::raw("to_char(a.arqueo_fec, 'DD/MM/YYYY HH24:MI') as arqueo_fec"),
                    'a.arqueo_tipo',
                    'a.arqueo_monto_sistema',
                    'a.arqueo_monto',
                    'a.arqueo_diferencia',
                    'a.arqueo_estado',
                    'c.caja_desc'
                )
                ->where('a.user_id', $userId)
                ->orderBy('a.arqueo_fec', 'desc')
                ->get();

            return response()->json($data, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'apertura_cierre_id' => 'required|integer',
                'user_id'            => 'required|integer',
                'arqueo_fec'         => 'required',
                'arqueo_tipo'        => 'required|string',
                'arqueo_monto'       => 'required|numeric|min:0'
            ]);

            $userId = $request->user_id;

            // 1. Verificar apertura existente y abierta
            $apertura = Aperturas_cierres::where('id', $request->apertura_cierre_id)
                ->where('estado', 'ABIERTA')
                ->first();

            if (!$apertura) {
                return response()->json([
                    'error'   => true,
                    'mensaje' => 'No existe una apertura activa para esta caja'
                ], 400);
            }

            // 2. Si es arqueo FINAL, verificar que no exista otro FINAL confirmado
            if ($request->arqueo_tipo === 'FINAL') {

                $existeFinal = Arqueo_caja::where('apertura_cierre_id', $apertura->id)
                    ->where('arqueo_tipo', 'FINAL')
                    ->where('arqueo_estado', 'CONFIRMADO')
                    ->exists();

                if ($existeFinal) {
                    return response()->json([
                        'error'   => true,
                        'mensaje' => 'Ya existe un arqueo FINAL confirmado para esta apertura'
                    ], 400);
                }
            }

            // 3. Calcular monto sistema
            $ingresos = DB::table('movimientos_caja')
                ->where('apertura_cierre_id', $apertura->id)
                ->where('mov_tipo', 'INGRESO')
                ->sum('mov_monto');

            $egresos = DB::table('movimientos_caja')
                ->where('apertura_cierre_id', $apertura->id)
                ->where('mov_tipo', 'EGRESO')
                ->sum('mov_monto');

            $montoSistema = $ingresos - $egresos;

            // 4. Calcular diferencia
            $montoArqueo = $request->arqueo_monto;
            $diferencia  = $montoArqueo - $montoSistema;

            // 5. Registrar arqueo
            $arqueo = Arqueo_caja::create([
                'apertura_cierre_id'   => $apertura->id,
                'user_id'              => $userId,
                'arqueo_fec'           => $request->arqueo_fec,
                'arqueo_tipo'          => $request->arqueo_tipo,
                'arqueo_monto_sistema' => $montoSistema,
                'arqueo_monto'         => $montoArqueo,
                'arqueo_diferencia'    => $diferencia,
                'arqueo_estado'        => 'REGISTRADO'
            ]);

            return response()->json([
                'error'   => false,
                'mensaje' => 'Arqueo registrado correctamente',
                'arqueo'  => $arqueo
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function confirmar($id)
    {
        try {

            // 1. Buscar arqueo por ID (viene de la ruta)
            $arqueo = Arqueo_caja::find($id);

            if (!$arqueo) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Arqueo no encontrado'
                ], 404);
            }

            // 2. Validar estado
            if ($arqueo->arqueo_estado !== 'REGISTRADO') {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Solo se puede confirmar un arqueo en estado REGISTRADO'
                ], 400);
            }

            // 3. Validar tipo
            if ($arqueo->arqueo_tipo !== 'FINAL') {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Solo se puede confirmar un arqueo FINAL'
                ], 400);
            }

            // 4. Confirmar
            $arqueo->update([
                'arqueo_estado' => 'CONFIRMADO'
            ]);

            return response()->json([
                'error' => false,
                'mensaje' => 'Arqueo confirmado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function anular($id)
    {
        try {

            $arqueo = Arqueo_caja::find($id);

            if (!$arqueo) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Arqueo no encontrado'
                ], 400);
            }

            if ($arqueo->arqueo_estado !== 'REGISTRADO') {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Solo se pueden anular arqueos en estado REGISTRADO'
                ], 400);
            }

            $arqueo->update([
                'arqueo_estado' => 'ANULADO'
            ]);

            return response()->json([
                'error' => false,
                'mensaje' => 'Arqueo anulado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    //funcion buscar
    public function buscar(Request $request)
    {
        try {

            $request->validate([
                'apertura_cierre_id' => 'required|integer'
            ]);

            $arqueo = DB::table('arqueo_caja')
                ->where('apertura_cierre_id', $request->apertura_cierre_id)
                ->where('arqueo_tipo', 'FINAL')
                ->where('arqueo_estado', 'CONFIRMADO')
                ->first();

            if (!$arqueo) {
                return response()->json([
                    'existe' => false,
                    'mensaje' => 'No existe arqueo FINAL confirmado'
                ], 200);
            }

            return response()->json([
                'existe'          => true,
                'arqueo_id'       => $arqueo->id,
                'monto_sistema'   => $arqueo->arqueo_monto_sistema,
                'monto_arqueo'    => $arqueo->arqueo_monto,
                'diferencia'      => $arqueo->arqueo_diferencia
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

}
