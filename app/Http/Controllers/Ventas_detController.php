<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ventas_det;

class Ventas_detController extends Controller
{
    public function read($id){
        return DB::select("
            SELECT 
            vd.*, 
            p.prod_desc,
            ti.id as impuesto_id, 
            ti.impuesto_desc
            FROM ventas_det vd
            JOIN productos p ON p.id = vd.producto_id
            JOIN tipo_impuestos ti ON ti.id = p.impuesto_id
            WHERE vd.venta_id = $id;"
        );
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            "venta_id"     => "required|exists:ventas_cab,id",
            "producto_id"   => "required|exists:productos,id",
            "venta_cant"   => "required|numeric|min:1",
            "venta_precio"  => "required|numeric|min:0"
        ]);

        $ventas_det = Ventas_det::create($datosValidados);

        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $ventas_det
        ], 200);
    }

    public function update(Request $request, $venta_id, $producto_id){
        $datosValidados = $request->validate([
            "venta_cant"   => "required|numeric|min:1",
            "venta_precio"  => "required|numeric|min:0"
        ]);

        DB::table('ventas_det')
            ->where('venta_id', $venta_id)
            ->where('producto_id', $producto_id)
            ->update([
                'venta_cant' => $datosValidados['venta_cant'],
                'venta_precio' => $datosValidados['venta_precio']
            ]);

        $actualizado = DB::select("
            SELECT * FROM ventas_det 
            WHERE venta_id = ? AND producto_id = ?
        ", [$venta_id, $producto_id]);

        return response()->json([
            'mensaje'=> 'Registro modificado con éxito',
            'tipo'=> 'success',
            'registro'=> $actualizado
        ], 200);
    }

    public function destroy($venta_id, $producto_id){
        DB::table('ventas_det')
            ->where('venta_id', $venta_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje'=> 'Registro eliminado con éxito',
            'tipo'=> 'success'
        ], 200);
    }
}
