<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pedidos_vent_det;
use App\Traits\VerificaPermisos;

class Pedidos_vent_detController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'movimientos/ventas_cobros/pedidos_ventas/';

    public function read($id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("SELECT pvd.*, p.prod_desc  
        FROM pedidos_vent_det pvd
        JOIN productos p ON p.id = pvd.producto_id
        WHERE pvd.pedido_vent_id = ?", [$id]);
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            "pedido_vent_id"     => "required",
            "producto_id"        => "required",
            "pedido_vent_cant"   => "required|numeric|min:1",
            "pedido_vent_precio" => "required|numeric|min:0"
        ]);

        $pedido_det = Pedidos_vent_det::create($datosValidados);
        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido_det
        ], 200);
    }

    public function update(Request $request, $pedido_vent_id, $producto_id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            "pedido_vent_cant"   => "required|numeric|min:1",
            "pedido_vent_precio" => "required|numeric|min:0"
        ]);

        DB::table('pedidos_vent_det')
            ->where('pedido_vent_id', $pedido_vent_id)
            ->where('producto_id', $producto_id)
            ->update($datosValidados);

        $pedido_det = DB::select("SELECT * FROM pedidos_vent_det WHERE pedido_vent_id = ? AND producto_id = ?", [$pedido_vent_id, $producto_id]);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido_det
        ], 200);
    }

    public function destroy($pedido_vent_id, $producto_id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular'); //aca deberia ser borrar pero no tengo ese permiso en la tabla, asi que uso anular
        if ($permiso) {
            return $permiso;
        }
        
        DB::table('pedidos_vent_det')
            ->where('pedido_vent_id', $pedido_vent_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }
}
