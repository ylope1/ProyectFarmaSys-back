<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Rendicion_ff_cab;
use App\Models\Rendicion_ff_det;

class Rendicion_ff_det_Controller extends Controller
{
    public function read($rendicion_ff_id)
    {
        return DB::select("
            SELECT
                rfd.*,
                d.documento_desc
            FROM rendicion_ff_det rfd
            JOIN documentos d ON d.id = rfd.documento_id
            WHERE rfd.rendicion_ff_id = ?
        ", [$rendicion_ff_id]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'rendicion_ff_id'        => 'required|exists:rendicion_ff_cab,id',
            'documento_id'           => 'required|exists:documentos,id',
            'rendicion_ff_det_monto' => 'required|numeric|min:1'
        ]);

        // Validar estado de la rendición
        $rendicion = Rendicion_ff_cab::find($request->rendicion_ff_id);

        if (!$rendicion || $rendicion->rendicion_ff_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden agregar documentos a rendiciones en estado REGISTRADO.',
                'tipo' => 'error'
            ], 400);
        }

        // Validar que el documento no esté ya rendido
        $existe = DB::table('rendicion_ff_det')
            ->where('documento_id', $request->documento_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Este documento ya fue rendido anteriormente.',
                'tipo' => 'error'
            ], 400);
        }

        $detalle = Rendicion_ff_det::create($datos);

        return response()->json([
            'mensaje'  => 'Documento agregado a la rendición.',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function update(Request $request, $rendicion_ff_id, $documento_id)
    {
        $datos = $request->validate([
            'rendicion_ff_det_monto' => 'required|numeric|min:1'
        ]);

        // Validar estado de la rendición
        $rendicion = Rendicion_ff_cab::find($rendicion_ff_id);

        if (!$rendicion || $rendicion->rendicion_ff_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden modificar documentos de rendiciones en estado REGISTRADO.',
                'tipo' => 'error'
            ], 400);
        }

        DB::table('rendicion_ff_det')
            ->where('rendicion_ff_id', $rendicion_ff_id)
            ->where('documento_id', $documento_id)
            ->update([
                'rendicion_ff_det_monto' => $datos['rendicion_ff_det_monto']
            ]);

        $actualizado = DB::select("
            SELECT
                rfd.*,
                d.documento_desc
            FROM rendicion_ff_det rfd
            JOIN documentos d ON d.id = rfd.documento_id
            WHERE rfd.rendicion_ff_id = ?
            AND rfd.documento_id = ?
        ", [$rendicion_ff_id, $documento_id]);

        return response()->json([
            'mensaje'  => 'Documento modificado correctamente.',
            'tipo'     => 'success',
            'registro' => $actualizado
        ], 200);
    }

    public function destroy($rendicion_ff_id, $documento_id)
    {
        // Validar estado de la rendición
        $rendicion = Rendicion_ff_cab::find($rendicion_ff_id);

        if (!$rendicion || $rendicion->rendicion_ff_estado !== 'REGISTRADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden eliminar documentos de rendiciones en estado REGISTRADO.',
                'tipo' => 'error'
            ], 400);
        }

        DB::table('rendicion_ff_det')
            ->where('rendicion_ff_id', $rendicion_ff_id)
            ->where('documento_id', $documento_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Documento eliminado de la rendición.',
            'tipo' => 'success'
        ], 200);
    }
}
