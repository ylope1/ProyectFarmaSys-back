<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo_impuesto;
use Illuminate\Support\Facades\DB;
use App\Traits\VerificaPermisos; 

class Tipo_impuestoController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'referenciales/compras/tipo_impuesto/';

    public function read(){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return Tipo_impuesto::all();
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            'impuesto_desc'=>'required'
        ]);
        $tipo_imp = Tipo_impuesto::create($datosValidados);
        $tipo_imp->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $tipo_imp
        ],200);
    }
    public function update(Request $request, $id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $tipo_imp = Tipo_impuesto::find($id);
        if(!$tipo_imp){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'impuesto_desc'=>'required'
        ]);
        $tipo_imp->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $tipo_imp
        ],200);

    }
    public function destroy ($id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular');
        if ($permiso) {
            return $permiso;
        }
        
        $tipo_imp = Tipo_impuesto::find($id);
        if(!$tipo_imp){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $tipo_imp->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar tipo impuesto
    public function buscar(Request $request){
        return DB::select("select ti.id as impuesto_id, ti.impuesto_desc 
        from tipo_impuestos ti
        where ti.impuesto_desc ilike '%$request->impuesto_desc%';");
    }
}
