<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notas_venta_det;

class Notas_venta_detController extends Controller
{
    public function read($id){ 
        return DB::select("
            SELECT 
                nvd.*, 
                p.prod_desc,
                ti.id as impuesto_id, 
                ti.impuesto_desc
            FROM notas_venta_det nvd
            JOIN productos p ON p.id = nvd.producto_id
            JOIN tipo_impuestos ti ON ti.id = p.impuesto_id
            WHERE nvd.nota_venta_id = ?;
        ", [$id]);
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            "nota_venta_id"     => "required|exists:notas_venta_cab,id",
            "producto_id"      => "required|exists:productos,id",
            "nota_venta_cant"      => "required|numeric|min:1",
            "nota_venta_precio"     => "required|numeric|min:0",
            "nota_venta_motivo" => "required|string|max:255"
        ]);

        $nota_det = Notas_venta_det::create($datosValidados);

        return response()->json([
            'mensaje'=> 'Detalle agregado con éxito',
            'tipo'=> 'success',
            'registro'=> $nota_det
        ], 200);
    }

    public function update(Request $request, $nota_venta_id, $producto_id){
        $datosValidados = $request->validate([
            "nota_venta_cant"      => "required|numeric|min:1",
            "nota_venta_precio"     => "required|numeric|min:0",
            "nota_venta_motivo" => "required|string|max:255"
        ]);

        DB::table('notas_venta_det')
            ->where('nota_venta_id', $nota_venta_id)
            ->where('producto_id', $producto_id)
            ->update([
                'nota_venta_cant' => $datosValidados['nota_venta_cant'],
                'nota_venta_precio' => $datosValidados['nota_venta_precio'],
                'nota_venta_motivo' => $datosValidados['nota_venta_motivo']
            ]);

        $actualizado = DB::select("
            SELECT * FROM notas_venta_det 
            WHERE nota_venta_id = ? AND producto_id = ?
        ", [$nota_venta_id, $producto_id]);

        return response()->json([
            'mensaje'=> 'Detalle modificado con éxito',
            'tipo'=> 'success',
            'registro'=> $actualizado
        ], 200);
    }

    public function destroy($nota_venta_id, $producto_id){
        DB::table('notas_venta_det')
            ->where('nota_venta_id', $nota_venta_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje'=> 'Detalle eliminado con éxito',
            'tipo'=> 'success'
        ], 200);
    }
}
