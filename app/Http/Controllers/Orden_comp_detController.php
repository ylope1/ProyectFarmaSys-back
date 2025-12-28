<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden_comp_cab;
use App\Models\Orden_comp_det;
use Illuminate\Support\Facades\DB;

class Orden_comp_detController extends Controller
{
    public function read($id){
        // Buscar la orden de compra para saber si tiene pedido, presupuesto o ambos
        $orden_comp_cab = DB::table('orden_comp_cab')->where('id', $id)->first();

        if (!$orden_comp_cab) {
            return response()->json(['mensaje' => 'Orden de compra no encontrada'], 404);
        }
        // Solo pedido
        if ($orden_comp_cab->pedido_comp_id && !$orden_comp_cab->presup_comp_id) {
            return DB::select("
                select 
                    pcd.pedido_comp_id as pedido_comp_id,
                    pcd.producto_id,
                    pcd.pedido_comp_cant as orden_comp_cant,
                    NULL as orden_comp_costo,
                    p.prod_desc
                from pedido_comp_det pcd
                join productos p on p.id = pcd.producto_id
                where pcd.pedido_comp_id = ?
            ", [$orden_comp_cab->pedido_comp_id]);
        }
        // Presupuesto (solo o junto con pedido)
        if ($orden_comp_cab->presup_comp_id) {
            return DB::select("
                select 
                    pcd.presup_comp_id as presup_comp_id,
                    pcd.producto_id,
                    pcd.presup_comp_cant as orden_comp_cant,
                    pcd.presup_comp_costo as orden_comp_costo,
                    p.prod_desc
                from presup_comp_det pcd
                join productos p on p.id = pcd.producto_id
                where pcd.presup_comp_id = ?
            ", [$orden_comp_cab->presup_comp_id]);
        }

        return response()->json(['mensaje' => 'No hay detalles para esta orden de compra'], 404);
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            "orden_comp_id"=> "required",
            "producto_id"=> "required",
            "orden_comp_cant"=> "required",
            "orden_comp_costo"=> "required"
        ]);
        $orden_comp_det = Orden_comp_det::create($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $orden_comp_det
        ],200);
    }

    public function update(Request $request, $orden_comp_id, $producto_id){
        $datosValidados = $request->validate([
            "orden_comp_cant"=> "required",
            "orden_comp_costo"=> "required"
        ]);

        $orden_comp_det = DB::table('orden_comp_det')
            ->where('orden_comp_id', $orden_comp_id)
            ->where('producto_id', $producto_id)
            ->update([
                'orden_comp_cant' => $datosValidados['orden_comp_cant'],
                'orden_comp_costo' => $datosValidados['orden_comp_costo']
            ]);

        $orden_comp_det = DB::select("select * from orden_comp_det where orden_comp_id = ? and producto_id = ?", [$orden_comp_id, $producto_id]);

        return response()->json([
            'mensaje'=> 'Registro modificado con éxito',
            'tipo'=> 'success',
            'registro'=> $orden_comp_det
        ],200);
    }

    public function destroy($orden_comp_id, $producto_id){
        DB::table('orden_comp_det')
            ->where('orden_comp_id', $orden_comp_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje'=> 'Registro eliminado con éxito',
            'tipo'=> 'success'
        ],200);
    }
}

