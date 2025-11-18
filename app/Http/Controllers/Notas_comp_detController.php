<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notas_comp_det;

class Notas_comp_detController extends Controller
{
    public function read($id){
        return DB::select("
            SELECT 
                ncd.*, 
                p.prod_desc,
                ti.id as impuesto_id, 
                ti.impuesto_desc
            FROM notas_comp_det ncd
            JOIN productos p ON p.id = ncd.producto_id
            JOIN tipo_impuestos ti ON ti.id = p.impuesto_id
            WHERE ncd.nota_comp_id = ?;
        ", [$id]);
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            "nota_comp_id"     => "required|exists:notas_comp_cab,id",
            "producto_id"      => "required|exists:productos,id",
            "compra_cant"      => "required|numeric|min:1",
            "compra_costo"     => "required|numeric|min:0",
            "nota_comp_motivo" => "required|string|max:255"
        ]);

        $nota_det = Notas_comp_det::create($datosValidados);

        return response()->json([
            'mensaje'=> 'Detalle agregado con éxito',
            'tipo'=> 'success',
            'registro'=> $nota_det
        ], 200);
    }

    public function update(Request $request, $nota_comp_id, $producto_id){
        $datosValidados = $request->validate([
            "compra_cant"      => "required|numeric|min:1",
            "compra_costo"     => "required|numeric|min:0",
            "nota_comp_motivo" => "required|string|max:255"
        ]);

        DB::table('notas_comp_det')
            ->where('nota_comp_id', $nota_comp_id)
            ->where('producto_id', $producto_id)
            ->update([
                'compra_cant' => $datosValidados['compra_cant'],
                'compra_costo' => $datosValidados['compra_costo'],
                'nota_comp_motivo' => $datosValidados['nota_comp_motivo']
            ]);

        $actualizado = DB::select("
            SELECT * FROM notas_comp_det 
            WHERE nota_comp_id = ? AND producto_id = ?
        ", [$nota_comp_id, $producto_id]);

        return response()->json([
            'mensaje'=> 'Detalle modificado con éxito',
            'tipo'=> 'success',
            'registro'=> $actualizado
        ], 200);
    }

    public function destroy($nota_comp_id, $producto_id){
        DB::table('notas_comp_det')
            ->where('nota_comp_id', $nota_comp_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje'=> 'Detalle eliminado con éxito',
            'tipo'=> 'success'
        ], 200);
    }
}
