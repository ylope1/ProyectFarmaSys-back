<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function read(){
        return DB::select("select prod.*, pr.proveedor_desc, i.item_desc, ti.impuesto_desc, m.marca_desc
        from productos prod
        join proveedores pr on pr.id = prod.proveedor_id  
        join items i on i.id = prod.item_id 
        join tipo_impuestos ti on ti.id = prod.impuesto_id
        join marcas m on m.id = prod.marca_id;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'prod_desc'=>'required',
            'prod_precio_comp'=>'required',
            'prod_precio_vent'=>'required',
            'proveedor_id'=>'required',
            'item_id'=>'required',
            'impuesto_id'=>'required',
            'marca_id'=>'required',
            'prod_estado'=>'required'
        ]);
        $producto = Producto::create($datosValidados);
        $producto->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $producto
        ],200);
    }
    public function update(Request $request, $id){
        $producto = Producto::find($id);
        if(!$producto){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'prod_desc'=>'required',
            'prod_precio_comp'=>'required',
            'prod_precio_vent'=>'required',
            'proveedor_id'=>'required',
            'item_id'=>'required',
            'impuesto_id'=>'required',
            'marca_id'=>'required',
            'prod_estado'=>'required'
        ]);
        $producto->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $producto
        ],200);

    }
    public function destroy ($id){
        $producto = Producto::find($id);
        if(!$producto){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $producto->update([
            'prod_estado' => 'INACTIVO'
        ]);
        return response()->json([
            'mensaje'=> 'Registro inactivado con exito',
            'tipo'=>'success'
        ],200);
    }
     // Función para buscar productos
     public function buscar(Request $request){
        return DB::select("select p.id as producto_id, p.* 
        from productos p
        WHERE p.prod_desc ILIKE ?
            AND p.prod_estado = 'ACTIVO'
        ", ['%' . $request->prod_desc . '%']);
    }
}

