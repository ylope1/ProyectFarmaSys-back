<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;

class FuncionarioController extends Controller
{
    public function read(){
        return DB::select("select f.*, c.ciudad_desc, ca.cargo_desc, u.login
from funcionarios f 
join ciudades c on c.id = f.ciudad_id 
join cargos ca on ca.id = f.cargo_id
join users u on u.id = f.user_id;");  
    }

    public function store(Request $request){
        $datosValidados = $request->validate([
            'func_nombre'=>'required',
            'func_apellido'=>'required',
            'func_ci'=>'required',
            'func_direc'=>'required',
            'func_telef'=>'required',
            'func_fec_nac'=>'required',
            'func_fec_baja'=>'required',
            'func_fec_ing'=>'required',
            'func_estado'=>'required',
            'ciudad_id'=>'required',
            'cargo_id'=>'required',
            'user_id'=>'required'
        ]);
        $funcionario = Funcionario::create($datosValidados);
        $funcionario->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $funcionario
        ],200);
    }
    public function update(Request $request, $id){
        $funcionario = Funcionario::find($id);
        if(!$funcionario){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $datosValidados = $request->validate([
            'func_nombre'=>'required',
            'func_apellido'=>'required',
            'func_ci'=>'required',
            'func_direc'=>'required',
            'func_telef'=>'required',
            'func_fec_nac'=>'required',
            'func_fec_baja'=>'required',
            'func_fec_ing'=>'required',
            'func_estado'=>'required',
            'ciudad_id'=>'required',
            'cargo_id'=>'required',
            'user_id'=>'required'
        ]);
        $funcionario->update($datosValidados);
        return response()->json([
            'mensaje'=> 'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $funcionario
        ],200);

    }
    public function destroy ($id){
        $funcionario = Funcionario::find($id);
        if(!$funcionario){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
        $funcionario->delete();
        return response()->json([
            'mensaje'=> 'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
    // Función para buscar funcionario
    public function buscar(Request $request){
        $query = $request->input('func_nombre'); // Obtener el valor de 'func_nombre' del frontend
        $funcionario = Funcionario::where('func_nombre', 'LIKE', "%{$query}%")->get(); // Filtrar funcionario por el nombre

        if($funcionario->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }
        return response()->json($funcionario, 200); // Retornar los resultados en formato JSON
    }
}
