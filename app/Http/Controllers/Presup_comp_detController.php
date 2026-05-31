<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presup_comp_det;
use Illuminate\Support\Facades\DB;


class Presup_comp_detController extends Controller
{
    public function read($id)
    {
        return DB::select("
            select 
                prd.*, 
                p.prod_desc  
            from presup_comp_det prd
            join productos p on p.id = prd.producto_id
            where prd.presup_comp_id = ?
            order by p.prod_desc
        ", [$id]);
    }
    public function store(Request $request){
        $datosValidados = $request->validate([
            "presup_comp_id" => "required|integer|exists:presup_comp_cab,id",
            "producto_id" => "required|integer|exists:productos,id",
            "presup_comp_cant" => "required|numeric|min:1",
            "presup_comp_costo" => "required|numeric|min:0"
        ]);
        $cabecera = DB::table('presup_comp_cab')
            ->where('id', $request->presup_comp_id)
            ->first();

        if (!$cabecera) {
            return response()->json([
                'mensaje' => 'No se encontró la cabecera del presupuesto',
                'tipo' => 'error'
            ], 404);
        }

        if ($cabecera->presup_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'No se puede agregar detalle a un presupuesto que no está pendiente',
                'tipo' => 'error'
            ], 400);
        }

        $existe = DB::table('presup_comp_det')
            ->where('presup_comp_id', $request->presup_comp_id)
            ->where('producto_id', $request->producto_id)
            ->first();

        if ($existe) {
            return response()->json([
                'mensaje' => 'El producto ya fue agregado al detalle del presupuesto',
                'tipo' => 'error'
            ], 400);
        }

        $presup_comp_det = Presup_comp_det::create($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $presup_comp_det
        ],200);
    }

    public function update(Request $request, $presup_comp_id, $producto_id){
        $request->validate([
            "presup_comp_cant" => "required|numeric|min:1",
            "presup_comp_costo" => "required|numeric|min:0"
        ]);

        $cabecera = DB::table('presup_comp_cab')
            ->where('id', $presup_comp_id)
            ->first();

        if (!$cabecera) {
            return response()->json([
                'mensaje' => 'No se encontró la cabecera del presupuesto',
                'tipo' => 'error'
            ], 404);
        }

        if ($cabecera->presup_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'No se puede modificar detalle de un presupuesto que no está pendiente',
                'tipo' => 'error'
            ], 400);
        }

        $detalle = DB::table('presup_comp_det')
            ->where('presup_comp_id', $presup_comp_id)
            ->where('producto_id', $producto_id)
            ->first();

        if (!$detalle) {
            return response()->json([
                'mensaje' => 'No se encontró el detalle del presupuesto',
                'tipo' => 'error'
            ], 404);
        }

        DB::table('presup_comp_det')
            ->where('presup_comp_id', $presup_comp_id)
            ->where('producto_id', $producto_id)
            ->update([
                'presup_comp_cant' => $request->presup_comp_cant,
                'presup_comp_costo' => $request->presup_comp_costo
            ]);

        $presup_comp_det = DB::select("
            select * 
            from presup_comp_det 
            where presup_comp_id = ? 
            and producto_id = ?
        ", [$presup_comp_id, $producto_id]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $presup_comp_det
        ], 200);
    }

    public function destroy($presup_comp_id, $producto_id){
        $cabecera = DB::table('presup_comp_cab')
            ->where('id', $presup_comp_id)
            ->first();

        if (!$cabecera) {
            return response()->json([
                'mensaje' => 'No se encontró la cabecera del presupuesto',
                'tipo' => 'error'
            ], 404);
        }

        if ($cabecera->presup_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'No se puede eliminar detalle de un presupuesto que no está pendiente',
                'tipo' => 'error'
            ], 400);
        }

        $detalle = DB::table('presup_comp_det')
            ->where('presup_comp_id', $presup_comp_id)
            ->where('producto_id', $producto_id)
            ->first();

        if (!$detalle) {
            return response()->json([
                'mensaje' => 'No se encontró el detalle del presupuesto',
                'tipo' => 'error'
            ], 404);
        }

        DB::table('presup_comp_det')
            ->where('presup_comp_id', $presup_comp_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo' => 'success',
            'registro' => $detalle
        ], 200);
    }

}

