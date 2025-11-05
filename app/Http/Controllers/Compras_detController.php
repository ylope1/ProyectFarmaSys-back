<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Compras_det;

class Compras_detController extends Controller
{
    public function read($id){
        return DB::select("
            SELECT 
            cd.*, 
            p.prod_desc, 
            ti.impuesto_desc
            FROM compras_det cd
            JOIN productos p ON p.id = cd.producto_id
            JOIN tipo_impuestos ti ON ti.id = p.impuesto_id
            WHERE cd.compra_id = $id;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            "compra_id"     => "required|exists:compras_cab,id",
            "producto_id"   => "required|exists:productos,id",
            "compra_cant"   => "required|numeric|min:1",
            "compra_costo"  => "required|numeric|min:0"
        ]);

        $compras_det = Compras_det::create($datosValidados);

        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $compras_det
        ], 200);
    }

    public function update(Request $request, $compra_id, $producto_id){
        $datosValidados = $request->validate([
            "compra_cant"   => "required|numeric|min:1",
            "compra_costo"  => "required|numeric|min:0"
        ]);

        DB::table('compras_det')
            ->where('compra_id', $compra_id)
            ->where('producto_id', $producto_id)
            ->update([
                'compra_cant' => $datosValidados['compra_cant'],
                'compra_costo' => $datosValidados['compra_costo']
            ]);

        $actualizado = DB::select("
            SELECT * FROM compras_det 
            WHERE compra_id = ? AND producto_id = ?
        ", [$compra_id, $producto_id]);

        return response()->json([
            'mensaje'=> 'Registro modificado con éxito',
            'tipo'=> 'success',
            'registro'=> $actualizado
        ], 200);
    }

    public function destroy($compra_id, $producto_id){
        DB::table('compras_det')
            ->where('compra_id', $compra_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje'=> 'Registro eliminado con éxito',
            'tipo'=> 'success'
        ], 200);
    }
}
