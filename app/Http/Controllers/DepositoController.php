<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deposito;
use Illuminate\Support\Facades\DB;
use App\Traits\VerificaPermisos; 

class DepositoController extends Controller
{
    use VerificaPermisos;
    private $rutaPermiso = 'referenciales/compras/depositos/';

    public function read(){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'ver');
        if ($permiso) {
            return $permiso;
        }

        return DB::select("select de.*, p.pais_desc, c.ciudad_desc, e.empresa_desc, s.suc_desc
        from depositos de
        join paises p on p.id = de.pais_id 
        join ciudades c on c.id = de.ciudad_id 
        join empresas e on e.id = de.empresa_id
        join sucursales s on s.id = de.sucursal_id;");
    }

    public function store(Request $request){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'crear');
        if ($permiso) {
            return $permiso;
        }

        $datosValidados = $request->validate([
            'deposito_desc'=>'required',
            'deposito_direc'=>'required',
            'deposito_telef'=>'required',
            'deposito_email'=>'required',
            'pais_id'=>'required',
            'ciudad_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $deposito = Deposito::create($datosValidados);
        $deposito->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $deposito
        ],200);
    }
    public function update(Request $request, $id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'modificar');
        if ($permiso) {
            return $permiso;
        }

        $deposito = Deposito::find($id);
        if(!$deposito){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'deposito_desc'=>'required',
            'deposito_direc'=>'required',
            'deposito_telef'=>'required',
            'deposito_email'=>'required',
            'pais_id'=>'required',
            'ciudad_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $deposito->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $deposito
        ],200);

    }
    public function destroy ($id){
        $permiso = $this->verificarPermiso($this->rutaPermiso, 'anular');
        if ($permiso) {
            return $permiso;
        }
        
        $deposito = Deposito::find($id);
        if(!$deposito){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $deposito->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar depositos
    public function buscar(Request $request){
        $query = $request->input('deposito_desc'); // Obtener el valor de 'deposito_desc' del frontend
        $deposito = Deposito::where('deposito_desc', 'LIKE', "%{$query}%")->get(); // Filtrar depositos por el nombre

        if($deposito->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }

        return response()->json($deposito, 200); // Retornar los resultados en formato JSON
    }
}
