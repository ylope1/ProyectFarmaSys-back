<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ajustes_cab;
use App\Models\Ajustes_det;

class Ajustes_detController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT 
            ad.*, 
            p.prod_desc,
            ti.id as impuesto_id, 
            ti.impuesto_desc,
            i.item_desc
            FROM ajustes_det ad
            JOIN productos p ON p.id = ad.producto_id
            JOIN tipo_impuestos ti ON ti.id = p.impuesto_id
            JOIN items i ON i.id = ad.item_id
            WHERE ad.ajuste_id = $id;"
        );
    }
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'ajuste_id'     => 'required|exists:ajustes_cab,id',
            'producto_id'   => 'required|exists:productos,id',
            'ajuste_cant'   => 'required|numeric|min:1',
            'ajuste_costo'  => 'required|numeric|min:0'
        ]);

        // Validar estado del ajuste
        $estado = DB::table('ajustes_cab')->where('id', $request->ajuste_id)->value('ajuste_estado');
        if ($estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'No se pueden agregar detalles. El ajuste ya fue confirmado o anulado.',
                'tipo' => 'error'
            ], 403);
        }

        // Obtener el item_id desde la tabla productos
        $producto = DB::table('productos')->where('id', $request->producto_id)->first();
        if (!$producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado.',
                'tipo' => 'error'
            ], 404);
        }

        $detalle = Ajustes_det::create([
            'ajuste_id'    => $request->ajuste_id,
            'producto_id'  => $request->producto_id,
            'ajuste_cant'  => $request->ajuste_cant,
            'ajuste_costo' => $request->ajuste_costo,
            'item_id'      => $producto->item_id
        ]);

        return response()->json([
            'mensaje' => 'Detalle agregado con éxito',
            'tipo' => 'success',
            'registro' => $detalle
        ], 200);
    }
    public function update(Request $request, $ajuste_id, $producto_id)
    {
        $datosValidados = $request->validate([
            "ajuste_cant"   => "required|numeric|min:1",
            "ajuste_costo"  => "required|numeric|min:0"
        ]);

        // Validar estado del ajuste
        $estado = DB::table('ajustes_cab')->where('id', $ajuste_id)->value('ajuste_estado');
        if ($estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'No se puede modificar el detalle. El ajuste ya fue confirmado o anulado.',
                'tipo' => 'error'
            ], 403);
        }

        // Obtener nuevamente el item_id desde productos
        $producto = DB::table('productos')->where('id', $producto_id)->first();
        if (!$producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado.',
                'tipo' => 'error'
            ], 404);
        }

        DB::table('ajustes_det')
            ->where('ajuste_id', $ajuste_id)
            ->where('producto_id', $producto_id)
            ->update([
                'ajuste_cant' => $datosValidados['ajuste_cant'],
                'ajuste_costo' => $datosValidados['ajuste_costo'],
                'item_id' => $producto->item_id
            ]);

        $actualizado = DB::select("
            SELECT * FROM ajustes_det 
            WHERE ajuste_id = ? AND producto_id = ?
        ", [$ajuste_id, $producto_id]);

        return response()->json([
            'mensaje'=> 'Registro modificado con éxito',
            'tipo'=> 'success',
            'registro'=> $actualizado
        ], 200);
    }
    public function destroy($ajuste_id, $producto_id){
        // Validar estado del ajuste
        $estado = DB::table('ajustes_cab')->where('id', $ajuste_id)->value('ajuste_estado');
        if ($estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'No se puede eliminar el detalle. El ajuste ya fue confirmado o anulado.',
                'tipo' => 'error'
            ], 403);
        }

        DB::table('ajustes_det')
            ->where('ajuste_id', $ajuste_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje'=> 'Registro eliminado con éxito',
            'tipo'=> 'success'
        ], 200);
    }
}
