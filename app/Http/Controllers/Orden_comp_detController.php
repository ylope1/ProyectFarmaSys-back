<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden_comp_cab;
use App\Models\Orden_comp_det;
use App\Traits\VerificaPermisos;
use Illuminate\Support\Facades\DB;

class Orden_comp_detController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'movimientos/compras/orden_compras/';
    
    public function read($id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("
            select 
                ocd.orden_comp_id,
                ocd.producto_id,
                ocd.orden_comp_cant,
                ocd.orden_comp_costo,
                p.prod_desc
            from orden_comp_det ocd
            join productos p on p.id = ocd.producto_id
            where ocd.orden_comp_id = ?
            order by p.prod_desc
        ", [$id]);
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            "orden_comp_id"=> "required",
            "producto_id"=> "required",
            "orden_comp_cant"=> "required",
            "orden_comp_costo"=> "required"
        ]);

        $orden = DB::table('orden_comp_cab')
            ->where('id', $request->orden_comp_id)
            ->first();

        if (!$orden) {
            return response()->json([
                'mensaje' => 'Orden de compra no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        if ($orden->orden_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede agregar detalle a una orden en estado PENDIENTE',
                'tipo' => 'error'
            ], 422);
        }

        $existe = DB::table('orden_comp_det')
            ->where('orden_comp_id', $request->orden_comp_id)
            ->where('producto_id', $request->producto_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'El producto ya existe en el detalle. Debe modificar el registro existente.',
                'tipo' => 'error'
            ], 422);
        }

        $orden_comp_det = Orden_comp_det::create($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $orden_comp_det
        ],200);
    }

    public function update(Request $request, $orden_comp_id, $producto_id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            "orden_comp_cant"=> "required|numeric|min:1",
            "orden_comp_costo"=> "required|numeric|min:0"
        ]);

        $orden = DB::table('orden_comp_cab')
            ->where('id', $orden_comp_id)
            ->first();

        if (!$orden) {
            return response()->json([
                'mensaje' => 'Orden de compra no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        if ($orden->orden_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede modificar detalle de una orden en estado PENDIENTE',
                'tipo' => 'error'
            ], 422);
        }

        $orden_comp_det = DB::table('orden_comp_det')
            ->where('orden_comp_id', $orden_comp_id)
            ->where('producto_id', $producto_id)
            ->update([
                'orden_comp_cant' => $datosValidados['orden_comp_cant'],
                'orden_comp_costo' => $datosValidados['orden_comp_costo']
            ]);

        $orden_comp_det = DB::select(
            "select * 
            from orden_comp_det 
            where orden_comp_id = ? and producto_id = ?
            ", [$orden_comp_id, $producto_id]);

        return response()->json([
            'mensaje'=> 'Registro modificado con éxito',
            'tipo'=> 'success',
            'registro'=> $orden_comp_det
        ],200);
    }

    public function destroy($orden_comp_id, $producto_id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular');
        if ($permiso) {
            return $permiso;
        }

        $orden = DB::table('orden_comp_cab')
            ->where('id', $orden_comp_id)
            ->first();

        if (!$orden) {
            return response()->json([
                'mensaje' => 'Orden de compra no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        if ($orden->orden_comp_estado != 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede eliminar detalle de una orden en estado PENDIENTE',
                'tipo' => 'error'
            ], 422);
        }

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

