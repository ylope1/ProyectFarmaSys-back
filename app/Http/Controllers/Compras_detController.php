<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\VerificaPermisos;
use App\Models\Compras_det;

class Compras_detController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'movimientos/compras/compras/';
    
    public function read($id)
    {
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("
            SELECT 
                cd.compra_id,
                cd.producto_id,
                p.prod_desc,
                cd.compra_cant,
                cd.compra_costo,
                ti.id AS impuesto_id,
                ti.impuesto_desc,

                (cd.compra_cant * cd.compra_costo) AS subtotal,

                CASE 
                    WHEN p.impuesto_id = 1 THEN ROUND(((cd.compra_cant * cd.compra_costo) / 1.10))
                    ELSE 0
                END AS grav_10,

                CASE 
                    WHEN p.impuesto_id = 1 THEN ROUND((cd.compra_cant * cd.compra_costo) - ((cd.compra_cant * cd.compra_costo) / 1.10))
                    ELSE 0
                END AS iva_10,

                CASE 
                    WHEN p.impuesto_id = 2 THEN ROUND(((cd.compra_cant * cd.compra_costo) / 1.05))
                    ELSE 0
                END AS grav_5,

                CASE 
                    WHEN p.impuesto_id = 2 THEN ROUND((cd.compra_cant * cd.compra_costo) - ((cd.compra_cant * cd.compra_costo) / 1.05))
                    ELSE 0
                END AS iva_5,

                CASE 
                    WHEN p.impuesto_id = 3 THEN ROUND((cd.compra_cant * cd.compra_costo))
                    ELSE 0
                END AS exentas

            FROM compras_det cd
            JOIN productos p ON p.id = cd.producto_id
            JOIN tipo_impuestos ti ON ti.id = p.impuesto_id
            WHERE cd.compra_id = ?
            ORDER BY p.prod_desc
        ", [$id]);
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            "compra_id"     => "required|exists:compras_cab,id",
            "producto_id"   => "required|exists:productos,id",
            "compra_cant"   => "required|numeric|min:1",
            "compra_costo"  => "required|numeric|min:0"
        ]);

        $compra = DB::table('compras_cab')
            ->where('id', $datosValidados['compra_id'])
            ->first();

        if (!$compra) {
            return response()->json([
                'mensaje' => 'Compra no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        if ($compra->compra_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede agregar detalle a compras en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        $existe = DB::table('compras_det')
            ->where('compra_id', $datosValidados['compra_id'])
            ->where('producto_id', $datosValidados['producto_id'])
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'El producto ya se encuentra agregado en el detalle de la compra',
                'tipo' => 'error'
            ], 400);
        }

        $compras_det = Compras_det::create($datosValidados);

        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $compras_det
        ], 200);
    }

    public function update(Request $request, $compra_id, $producto_id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $compra = DB::table('compras_cab')
            ->where('id', $compra_id)
            ->first();

        if (!$compra) {
            return response()->json([
                'mensaje' => 'Compra no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        if ($compra->compra_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede modificar detalle de compras en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        $detalle = DB::table('compras_det')
            ->where('compra_id', $compra_id)
            ->where('producto_id', $producto_id)
            ->first();

        if (!$detalle) {
            return response()->json([
                'mensaje' => 'Detalle de compra no encontrado',
                'tipo' => 'error'
            ], 404);
        }

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
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular'); //aca deberia ser borrar pero no tengo ese permiso en la tabla, asi que uso anular
        if ($permiso) {
            return $permiso;
        }
        
        $compra = DB::table('compras_cab')
            ->where('id', $compra_id)
            ->first();

        if (!$compra) {
            return response()->json([
                'mensaje' => 'Compra no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        if ($compra->compra_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede eliminar detalle de compras en estado PENDIENTE',
                'tipo' => 'error'
            ], 400);
        }

        $detalle = DB::table('compras_det')
            ->where('compra_id', $compra_id)
            ->where('producto_id', $producto_id)
            ->first();

        if (!$detalle) {
            return response()->json([
                'mensaje' => 'Detalle de compra no encontrado',
                'tipo' => 'error'
            ], 404);
        }
        
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
