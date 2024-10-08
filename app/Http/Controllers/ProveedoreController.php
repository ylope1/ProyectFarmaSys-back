<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedore;
use Illuminate\Support\Facades\DB;

class ProveedoreController extends Controller
{
    public function read(){
        return DB::select("select pr.*, p.pais_desc, c.ciudad_desc 
from proveedores pr
join paises p on p.id = pr.pais_id 
join ciudades c on c.id = pr.ciudad_id;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'proveedor_desc'=>'required',
            'proveedor_ruc'=>'required',
            'proveedor_tipo'=>'required',
            'proveedor_direc'=>'required',
            'proveedor_telef'=>'required',
            'proveedor_email'=>'required',
            'pais_id'=>'required',
            'ciudad_id'=>'required'
        ]);
        $proveedore = Proveedore::create($datosValidados);
        $proveedore->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $proveedore
        ],200);
    }
    public function update(Request $request, $id){
        $proveedore = Proveedore::find($id);
        if(!$proveedore){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'proveedor_desc'=>'required',
            'proveedor_ruc'=>'required',
            'proveedor_tipo'=>'required',
            'proveedor_direc'=>'required',
            'proveedor_telef'=>'required',
            'proveedor_email'=>'required',
            'pais_id'=>'required',
            'ciudad_id'=>'required'
        ]);
        $proveedore->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $proveedore
        ],200);

    }
    public function destroy ($id){
        $proveedore = Proveedore::find($id);
        if(!$proveedore){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $proveedore->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar proveedores
    public function buscar(Request $request){
        $query = $request->input('proveedor_desc'); // Obtener el valor de 'proveedor_desc' del frontend
        $proveedore = Proveedore::where('proveedor_desc', 'LIKE', "%{$query}%")->get(); // Filtrar proveedores por el nombre

        if($proveedore->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }
        return response()->json($proveedore, 200); // Retornar los resultados en formato JSON
    }
}
