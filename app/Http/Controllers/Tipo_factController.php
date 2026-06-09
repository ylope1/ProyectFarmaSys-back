<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo_fact;
use App\Traits\VerificaPermisos; 

class Tipo_factController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'referenciales/ventas/tipo_facturas/';

    public function read(){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return Tipo_fact::all();
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            'tipo_fact_desc'=>'required'
        ]);
        $tipo_fact = Tipo_fact::create($datosValidados);
        $tipo_fact->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $tipo_fact
        ],200);
    }
    public function update(Request $request, $id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $tipo_fact = Tipo_fact::find($id);
        if(!$tipo_fact){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'tipo_fact_desc'=>'required'
        ]);
        $tipo_fact->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $tipo_fact
        ],200);

    }
    public function destroy ($id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular');
        if ($permiso) {
            return $permiso;
        }
        
        $tipo_fact = Tipo_fact::find($id);
        if(!$tipo_fact){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $tipo_fact->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar tipos de facturas
    public function buscar(Request $request){
        $query = $request->input('tipo_fact_desc'); // Obtener el valor de 'tipo_fact_desc' del frontend
        $tipo_fact = Tipo_fact::where('tipo_fact_desc', 'LIKE', "%{$query}%")->get(); // Filtrar tipo de facturas por la descripcion
        if($tipo_fact->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }
        return response()->json($tipo_fact, 200); // Retornar los resultados en formato JSON
    }
}
