<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Mov_bancario;

class Mov_bancariosController extends Controller
{
    public function read()
    {
        return DB::select("
            select
                mb.id,
                mb.mov_banc_fecha,
                mb.mov_banc_tipo,
                mb.mov_banc_nro_ref,
                mb.mov_banc_fec_emision,
                mb.mov_banc_fec_valor,
                mb.mov_banc_monto_debito,
                mb.mov_banc_monto_credito,
                mb.mov_banc_estado,
                cb.cta_banc_banco,
                cb.cta_banc_nro_cuenta,
                t.tit_nombre || ' ' || t.tit_apellido as titular,
                u.name as usuario,
                s.suc_desc
            from mov_bancarios mb
            join cta_bancarias cb 
                on cb.id = mb.cta_bancaria_id
            join titulares t 
                on t.id = mb.titular_id
            join users u 
                on u.id = mb.user_id
            join sucursales s 
                on s.id = mb.sucursal_id
            order by mb.mov_banc_fecha desc
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'cta_bancaria_id'        => 'required',
            'titular_id'             => 'required',
            'mov_banc_fecha'         => 'required',
            'mov_banc_tipo'          => 'required',
            'mov_banc_nro_ref'       => 'nullable',
            'mov_banc_fec_emision'   => 'nullable',
            'mov_banc_fec_valor'     => 'nullable',
            'mov_banc_monto_debito'  => 'nullable',
            'mov_banc_monto_credito' => 'nullable',
            'mov_banc_estado'        => 'required',
            'user_id'                => 'required',
            'sucursal_id'            => 'required',
            'observacion'            => 'nullable'
        ]);

        $movimiento = Mov_bancario::create($datosValidados);
        $movimiento->save();

        return response()->json([
            'mensaje'  => 'Movimiento bancario registrado con éxito',
            'tipo'     => 'success',
            'registro' => $movimiento
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $movimiento = Mov_bancario::find($id);

        if (!$movimiento) {
            return response()->json([
                'mensaje' => 'Movimiento bancario no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'mov_banc_fecha'         => 'required',
            'mov_banc_tipo'          => 'required',
            'mov_banc_nro_ref'       => 'nullable',
            'mov_banc_fec_emision'   => 'nullable',
            'mov_banc_fec_valor'     => 'nullable',
            'mov_banc_monto_debito'  => 'nullable',
            'mov_banc_monto_credito' => 'nullable',
            'mov_banc_estado'        => 'required',
            'observacion'            => 'nullable'
        ]);

        $movimiento->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Movimiento bancario modificado con éxito',
            'tipo'     => 'success',
            'registro' => $movimiento
        ], 200);
    }

    public function anular(Request $request, $id)
    {
        $movimiento = Mov_bancario::find($id);

        if (!$movimiento) {
            return response()->json([
                'mensaje' => 'Movimiento bancario no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($movimiento->mov_banc_estado === 'ANULADO') {
            return response()->json([
                'mensaje' => 'El movimiento ya se encuentra anulado',
                'tipo'    => 'error'
            ], 400);
        }

        $movimiento->mov_banc_estado = 'ANULADO';
        $movimiento->save();

        return response()->json([
            'mensaje'  => 'Movimiento bancario anulado con éxito',
            'tipo'     => 'success',
            'registro' => $movimiento
        ], 200);
    }

    public function confirmar($id)
    {
        $movimiento = Mov_bancario::find($id);

        if (!$movimiento) {
            return response()->json([
                'mensaje' => 'Movimiento bancario no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($movimiento->mov_banc_estado === 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'El movimiento ya fue confirmado',
                'tipo'    => 'error'
            ], 400);
        }

        $movimiento->mov_banc_estado = 'CONFIRMADO';
        $movimiento->save();

        return response()->json([
            'mensaje'  => 'Movimiento bancario confirmado con éxito',
            'tipo'     => 'success',
            'registro' => $movimiento
        ], 200);
    }

    public function buscar(Request $r)
    {
        $condiciones = "";

        if ($r->cta_bancaria_id) {
            $condiciones .= " and mb.cta_bancaria_id = $r->cta_bancaria_id";
        }

        if ($r->mov_banc_tipo) {
            $condiciones .= " and mb.mov_banc_tipo = '$r->mov_banc_tipo'";
        }

        if ($r->mov_banc_estado) {
            $condiciones .= " and mb.mov_banc_estado = '$r->mov_banc_estado'";
        }

        return DB::select("
            select
                mb.id,
                mb.mov_banc_fecha,
                mb.mov_banc_tipo,
                mb.mov_banc_nro_ref,
                mb.mov_banc_monto_debito,
                mb.mov_banc_monto_credito,
                mb.mov_banc_estado,
                cb.cta_banc_banco,
                cb.cta_banc_nro_cuenta
            from mov_bancarios mb
            join cta_bancarias cb on cb.id = mb.cta_bancaria_id
            where 1=1
            $condiciones
            order by mb.mov_banc_fecha desc
        ");
    }
}
