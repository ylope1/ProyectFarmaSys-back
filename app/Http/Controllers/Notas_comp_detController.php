<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notas_comp_cab;
use App\Models\Notas_comp_det;
use App\Traits\VerificaPermisos;

class Notas_comp_detController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'movimientos/compras/notas_cred_deb/';
    
    public function read($id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

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
            ORDER BY p.prod_desc
        ", [$id]);
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            "nota_comp_id"     => "required|exists:notas_comp_cab,id",
            "producto_id"      => "required|exists:productos,id",
            "compra_cant"      => "required|numeric|min:1",
            "compra_costo"     => "required|numeric|min:0",
            "nota_comp_motivo" => "required|string|max:255"
        ]);

        return DB::transaction(function () use ($datosValidados) {

            $nota = Notas_comp_cab::find($datosValidados['nota_comp_id']);

            if (!$nota) {
                return response()->json([
                    'mensaje' => 'Nota no encontrada.',
                    'tipo' => 'error'
                ], 404);
            }

            if ($nota->nota_comp_estado !== 'PENDIENTE') {
                return response()->json([
                    'mensaje' => 'Solo se pueden agregar detalles a notas en estado PENDIENTE.',
                    'tipo' => 'error'
                ], 400);
            }

            $existeDetalle = Notas_comp_det::where('nota_comp_id', $datosValidados['nota_comp_id'])
                ->where('producto_id', $datosValidados['producto_id'])
                ->exists();

            if ($existeDetalle) {
                return response()->json([
                    'mensaje' => 'El producto ya existe en el detalle de la nota.',
                    'tipo' => 'error'
                ], 400);
            }

            $productoEnCompra = DB::table('compras_det')
                ->where('compra_id', $nota->compra_id)
                ->where('producto_id', $datosValidados['producto_id'])
                ->first();

            if (!$productoEnCompra) {
                return response()->json([
                    'mensaje' => 'El producto seleccionado no pertenece a la compra origen.',
                    'tipo' => 'error'
                ], 400);
            }

            if ($datosValidados['compra_cant'] > $productoEnCompra->compra_cant) {
                return response()->json([
                    'mensaje' => 'La cantidad de la nota no puede superar la cantidad de la compra origen.',
                    'tipo' => 'error'
                ], 400);
            }

            $nota_det = Notas_comp_det::create($datosValidados);

            return response()->json([
                'mensaje'=> 'Detalle agregado con éxito',
                'tipo'=> 'success',
                'registro'=> $nota_det
            ], 200);
        });
    }

    public function update(Request $request, $nota_comp_id, $producto_id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            "compra_cant"      => "required|numeric|min:1",
            "compra_costo"     => "required|numeric|min:0",
            "nota_comp_motivo" => "required|string|max:255"
        ]);

        return DB::transaction(function () use ($datosValidados, $nota_comp_id, $producto_id) {

            $nota = Notas_comp_cab::find($nota_comp_id);

            if (!$nota) {
                return response()->json([
                    'mensaje' => 'Nota no encontrada.',
                    'tipo' => 'error'
                ], 404);
            }

            if ($nota->nota_comp_estado !== 'PENDIENTE') {
                return response()->json([
                    'mensaje' => 'Solo se pueden modificar detalles de notas en estado PENDIENTE.',
                    'tipo' => 'error'
                ], 400);
            }

            $detalle = Notas_comp_det::where('nota_comp_id', $nota_comp_id)
                ->where('producto_id', $producto_id)
                ->first();

            if (!$detalle) {
                return response()->json([
                    'mensaje' => 'Detalle no encontrado.',
                    'tipo' => 'error'
                ], 404);
            }

            $productoEnCompra = DB::table('compras_det')
                ->where('compra_id', $nota->compra_id)
                ->where('producto_id', $producto_id)
                ->first();

            if (!$productoEnCompra) {
                return response()->json([
                    'mensaje' => 'El producto no pertenece a la compra origen.',
                    'tipo' => 'error'
                ], 400);
            }

            if ($datosValidados['compra_cant'] > $productoEnCompra->compra_cant) {
                return response()->json([
                    'mensaje' => 'La cantidad de la nota no puede superar la cantidad de la compra origen.',
                    'tipo' => 'error'
                ], 400);
            }

            DB::table('notas_comp_det')
                ->where('nota_comp_id', $nota_comp_id)
                ->where('producto_id', $producto_id)
                ->update([
                    'compra_cant' => $datosValidados['compra_cant'],
                    'compra_costo' => $datosValidados['compra_costo'],
                    'nota_comp_motivo' => $datosValidados['nota_comp_motivo']
                ]);

            $actualizado = DB::select("
                SELECT 
                    ncd.*, 
                    p.prod_desc,
                    ti.id AS impuesto_id,
                    ti.impuesto_desc,
                    (ncd.compra_cant * ncd.compra_costo) AS subtotal
                FROM notas_comp_det ncd
                JOIN productos p ON p.id = ncd.producto_id
                JOIN tipo_impuestos ti ON ti.id = p.impuesto_id
                WHERE ncd.nota_comp_id = ? 
                AND ncd.producto_id = ?
            ", [$nota_comp_id, $producto_id]);

            return response()->json([
                'mensaje'=> 'Detalle modificado con éxito',
                'tipo'=> 'success',
                'registro'=> $actualizado
            ], 200);
        });
    }

    public function destroy($nota_comp_id, $producto_id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular'); //aca deberia ser borrar pero no tengo ese permiso en la tabla, asi que uso anular
        if ($permiso) {
            return $permiso;
        }
        
        return DB::transaction(function () use ($nota_comp_id, $producto_id) {
            $nota = Notas_comp_cab::find($nota_comp_id);

            if (!$nota) {
                return response()->json([
                    'mensaje' => 'Nota no encontrada.',
                    'tipo' => 'error'
                ], 404);
            }

            if ($nota->nota_comp_estado !== 'PENDIENTE') {
                return response()->json([
                    'mensaje' => 'Solo se pueden eliminar detalles de notas en estado PENDIENTE.',
                    'tipo' => 'error'
                ], 400);
            }

            $detalle = Notas_comp_det::where('nota_comp_id', $nota_comp_id)
                ->where('producto_id', $producto_id)
                ->first();

            if (!$detalle) {
                return response()->json([
                    'mensaje' => 'Detalle no encontrado.',
                    'tipo' => 'error'
                ], 404);
            }

            DB::table('notas_comp_det')
                ->where('nota_comp_id', $nota_comp_id)
                ->where('producto_id', $producto_id)
                ->delete();

            return response()->json([
                'mensaje'=> 'Detalle eliminado con éxito',
                'tipo'=> 'success'
            ], 200);
        });
    }
}
