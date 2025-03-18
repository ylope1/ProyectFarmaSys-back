<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursale;
use Illuminate\Support\Facades\DB;

class SucursaleController extends Controller
{
    public function read(){
        return DB::select("select s.*, p.pais_desc, c.ciudad_desc, e.empresa_desc 
from sucursales s 
join paises p on p.id = s.pais_id 
join ciudades c on c.id = s.ciudad_id 
join empresas e on e.id = s.empresa_id;");
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'suc_desc'=>'required',
            'suc_direc'=>'required',
            'suc_telef'=>'required',
            'suc_email'=>'required',
            'pais_id'=>'required',
            'ciudad_id'=>'required',
            'empresa_id'=>'required'
        ]);
        $sucursale = Sucursale::create($datosValidados);
        $sucursale->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $sucursale
        ],200);
    }
    public function update(Request $request, $id){
        $sucursale = Sucursale::find($id);
        if(!$sucursale){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'suc_desc'=>'required',
            'suc_direc'=>'required',
            'suc_telef'=>'required',
            'suc_email'=>'required',
            'pais_id'=>'required',
            'ciudad_id'=>'required',
            'empresa_id'=>'required'
        ]);
        $sucursale->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $sucursale
        ],200);

    }
    public function destroy ($id){
        $sucursale = Sucursale::find($id);
        if(!$sucursale){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $sucursale->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
     /*// Función para buscar sucursal
     public function buscar(Request $request){
        $query = $request->input('suc_desc'); // Obtener el valor de 'suc_desc' del frontend
        $sucursale = Sucursale::where('suc_desc', 'LIKE', "%{$query}%")->get(); // Filtrar sucursal por la descripcion

        if($sucursale->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }
        return response()->json($sucursale, 200); // Retornar los resultados en formato JSON
    }*/
    public function buscar(Request $r){
        return DB::select("select s.id as sucursal_id, s.* 
        from sucursales s
        where s.suc_desc ilike '%$r->suc_desc%';");
    }
}
