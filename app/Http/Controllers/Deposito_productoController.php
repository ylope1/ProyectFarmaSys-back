<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deposito_producto;
use Illuminate\Support\Facades\DB;

class Deposito_productoController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT dp.*, d.deposito_desc, p.prod_desc
            FROM deposito_productos dp
            JOIN depositos d ON d.id = dp.deposito_id
            JOIN productos p ON p.id = dp.producto_id
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'deposito_id' => 'required|integer|exists:depositos,id',
            'producto_id' => 'required|integer|exists:productos,id',
            'cantidad' => 'required|numeric|min:1',
            'fecha_movimiento' => 'required|date',
            'motivo' => 'required|string|max:50',
        ]);

        $registro = Deposito_producto::create($datosValidados);

        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $registro
        ], 200);
    }

    public function update(Request $request, $deposito_id, $producto_id)
    {
        $registro = Deposito_producto::where('deposito_id', $deposito_id)
                                    ->where('producto_id', $producto_id)
                                    ->first();

        if (!$registro) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'cantidad' => 'required|numeric|min:0',
            'fecha_movimiento' => 'nullable|date',
            'motivo' => 'nullable|string|max:50',
        ]);

        $registro->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $registro
        ], 200);
    }

    public function destroy($deposito_id, $producto_id)
    {
        $registro = DB::table('deposito_productos')
        ->where('deposito_id', $deposito_id)
        ->where('producto_id', $producto_id)
        ->first();

        if (!$registro) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        DB::table('deposito_productos')
            ->where('deposito_id', $deposito_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo' => 'success'
        ], 200);
    }
}

