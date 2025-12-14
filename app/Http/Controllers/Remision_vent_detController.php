<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Remision_vent_det;

class Remision_vent_detController extends Controller
{
    public function read($id){
        return DB::select("
            SELECT 
                rvd.*, 
                p.prod_desc,
                ti.id as impuesto_id, 
                ti.impuesto_desc
            FROM remision_vent_det rvd
            JOIN productos p ON p.id = rvd.producto_id
            JOIN tipo_impuestos ti ON ti.id = p.impuesto_id
            WHERE rvd.remision_vent_id = ?
        ", [$id]);
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            "remision_vent_id"     => "required|exists:remision_vent_cab,id",
            "producto_id"   => "required|exists:productos,id",
            "remision_vent_cant"   => "required|numeric|min:1",
            "remision_vent_precio"  => "required|numeric|min:0",
            "remision_vent_obs"  => "nullable|string|max:255"
        ]);

        // Verificar duplicados
        if (Remision_vent_det::where('remision_vent_id', $request->remision_vent_id)
            ->where('producto_id', $request->producto_id)
            ->exists()) 
        {
            return response()->json([
                'mensaje' => 'El producto ya está en el detalle.',
                'tipo' => 'error'
            ], 422);
        }

        $remision_vent_det = Remision_vent_det::create($datosValidados);

        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $remision_vent_det
        ], 200);
    }

    public function update(Request $request, $remision_vent_id, $producto_id){
        $datosValidados = $request->validate([
            "remision_vent_cant"   => "required|numeric|min:1",
            "remision_vent_precio"  => "required|numeric|min:0",
            "remision_vent_obs"  => "nullable|string|max:255"
        ]);

        // Verificar estado cabecera
        $estado = DB::table('remision_vent_cab')->where('id', $remision_vent_id)->value('remision_vent_estado');
        if (in_array($estado, ['ENTREGADO', 'ANULADO'])) {
            return response()->json([
                'mensaje' => 'No se pueden editar productos porque la remisión está ENTREGADA o ANULADA.',
                'tipo' => 'error'
            ], 422);
        }

        DB::table('remision_vent_det')
            ->where('remision_vent_id', $remision_vent_id)
            ->where('producto_id', $producto_id)
            ->update([
                'remision_vent_cant' => $datosValidados['remision_vent_cant'],
                'remision_vent_precio' => $datosValidados['remision_vent_precio'],
                'remision_vent_obs' => $datosValidados['remision_vent_obs'] ?? null
            ]);

        $actualizado = DB::table('remision_vent_det')
            ->where('remision_vent_id', $remision_vent_id)
            ->where('producto_id', $producto_id)
            ->first();

        return response()->json([
            'mensaje'=> 'Registro modificado con éxito',
            'tipo'=> 'success',
            'registro'=> $actualizado
        ], 200);
    }

    public function destroy($remision_vent_id, $producto_id){
        
        // Verificar estado cabecera
        $estado = DB::table('remision_vent_cab')->where('id', $remision_vent_id)->value('remision_vent_estado');
        if (in_array($estado, ['ENTREGADO', 'ANULADO'])) {
            return response()->json([
                'mensaje' => 'No se pueden eliminar productos porque la remisión está ENTREGADA o ANULADA.',
                'tipo' => 'error'
            ], 422);
        }
        
        DB::table('remision_vent_det')
            ->where('remision_vent_id', $remision_vent_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje'=> 'Registro eliminado con éxito',
            'tipo'=> 'success'
        ], 200);
    }
}
