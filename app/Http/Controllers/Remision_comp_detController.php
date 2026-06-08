<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Remision_comp_det;
use App\Traits\VerificaPermisos;

class Remision_comp_detController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'movimientos/compras/nota_remision/';

    public function read($id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("
            SELECT 
            rcd.*, 
            p.prod_desc,
            ti.id as impuesto_id, 
            ti.impuesto_desc
            FROM remision_comp_det rcd
            JOIN productos p ON p.id = rcd.producto_id
            JOIN tipo_impuestos ti ON ti.id = p.impuesto_id
            WHERE rcd.remision_comp_id = $id;");
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            "remision_comp_id"     => "required|exists:remision_comp_cab,id",
            "producto_id"   => "required|exists:productos,id",
            "rem_comp_cant"   => "required|numeric|min:1",
            "rem_comp_costo"  => "required|numeric|min:0",
            "rem_comp_obs"  => "nullable|string|max:255"
        ]);

        $remision_comp_det = Remision_comp_det::create($datosValidados);

        return response()->json([
            'mensaje'=> 'Registro creado con éxito',
            'tipo'=> 'success',
            'registro'=> $remision_comp_det
        ], 200);
    }

    public function update(Request $request, $remision_comp_id, $producto_id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            "rem_comp_cant"   => "required|numeric|min:1",
            "rem_comp_costo"  => "required|numeric|min:0",
            "rem_comp_obs"  => "nullable|string|max:255"
        ]);

        DB::table('remision_comp_det')
            ->where('remision_comp_id', $remision_comp_id)
            ->where('producto_id', $producto_id)
            ->update([
                'rem_comp_cant' => $datosValidados['rem_comp_cant'],
                'rem_comp_costo' => $datosValidados['rem_comp_costo'],
                'rem_comp_obs' => $datosValidados['rem_comp_obs'] ?? null
            ]);

        $actualizado = DB::select("
            SELECT * FROM remision_comp_det 
            WHERE remision_comp_id = ? AND producto_id = ?
        ", [$remision_comp_id, $producto_id]);

        return response()->json([
            'mensaje'=> 'Registro modificado con éxito',
            'tipo'=> 'success',
            'registro'=> $actualizado
        ], 200);
    }

    public function destroy($remision_comp_id, $producto_id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular'); //aca deberia ser borrar pero no tengo ese permiso en la tabla, asi que uso anular
        if ($permiso) {
            return $permiso;
        }
        
        DB::table('remision_comp_det')
            ->where('remision_comp_id', $remision_comp_id)
            ->where('producto_id', $producto_id)
            ->delete();

        return response()->json([
            'mensaje'=> 'Registro eliminado con éxito',
            'tipo'=> 'success'
        ], 200);
    }
}
