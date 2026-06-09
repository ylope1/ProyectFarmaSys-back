<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Entidades_emisoras;
use App\Traits\VerificaPermisos; 

class Entidades_emisorasController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'referenciales/ventas/entidad_emisora/';

    public function read(){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("select * from entidades_emisoras;");
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            'ent_emi_desc' => 'required',
            'ent_emi_direc' => 'nullable',
            'ent_emi_telef' => 'nullable',
            'ent_emi_email' => 'nullable',
            'ent_emi_estado' => 'required'
        ]);

        $entidadEmisora = Entidades_emisoras::create($datosValidados);
        $entidadEmisora->save();

        return response()->json([
            'mensaje' => 'Registro creado con exito',
            'tipo' => 'success',
            'registro' => $entidadEmisora
        ], 200);
    }

    public function update(Request $request, $id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $entidadEmisora = Entidades_emisoras::find($id);

        if(!$entidadEmisora){
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'ent_emi_desc' => 'required',
            'ent_emi_direc' => 'nullable',
            'ent_emi_telef' => 'nullable',
            'ent_emi_email' => 'nullable',
            'ent_emi_estado' => 'required'
        ]);

        $entidadEmisora->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con exito',
            'tipo' => 'success',
            'registro' => $entidadEmisora
        ], 200);
    }

    public function destroy($id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular');
        if ($permiso) {
            return $permiso;
        }
        
        $entidadEmisora = Entidades_emisoras::find($id);

        if(!$entidadEmisora){
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $entidadEmisora->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con exito',
            'tipo' => 'success'
        ], 200);
    }

    public function buscar(Request $r){
        return DB::select("
            select *
            from entidades_emisoras
            where ent_emi_desc ilike '%$r->entidad_desc%';
        ");
    }
}
