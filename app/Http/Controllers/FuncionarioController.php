<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use App\Models\Funcionario;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FuncionarioController extends Controller
{
    public function read(){
        return DB::select("select 
        f.id,
        p.pers_nombre, 
        p.pers_apellido,
        p.pers_ci,
        p.pers_direc,
        p.pers_telef,
        p.pers_email,
        p.pais_id,
        p.ciudad_id,
        f.func_fec_nac,
        f.func_fec_ing,
        f.func_fec_baja,
        f.func_estado,
        f.cargo_id,
        f.user_id,
        u.login,
        ca.cargo_desc,
        c.ciudad_desc,
        pa.pais_desc
        from funcionarios f
        join personas p on p.id = f.persona_id
        join ciudades c on c.id = p.ciudad_id
        join paises pa on pa.id = p.pais_id 
        join cargos ca on ca.id = f.cargo_id
        join users u on u.id = f.user_id;");  
    }

    public function store(Request $request){
        $validatedPersona = $request->validate([
            'pers_nombre'=>'required',
            'pers_apellido'=>'required',
            'pers_ci'=>'required|unique:personas,pers_ci',
            'pers_direc'=>'required',
            'pers_telef'=>'required',
            'pers_email'=>'required|email|unique:personas,pers_email',
            'pais_id'=>'required',
            'ciudad_id'=>'required'
        ]);
            $validatedFuncionario = $request->validate([
            'func_fec_nac' => 'required|date',
            'func_fec_baja' => 'nullable|date',
            'func_fec_ing' => 'required|date',
            'func_estado' => 'required',
            'cargo_id' => 'required',
            'user_id' => 'required'
        ]);

        return DB::transaction(function () use ($validatedPersona, $validatedFuncionario) {
            $persona = Persona::create($validatedPersona);

            $funcionario = Funcionario::create(array_merge($validatedFuncionario, [
                'persona_id' => $persona->id
            ]));
            return response()->json([
                'mensaje'=> 'Registro creado con exito',
                'tipo'=>'success',
                'registro'=> $funcionario->load('persona')
            ],200);
        });
    }
    public function update(Request $request, $id){
        $funcionario = Funcionario::find($id);
        if(!$funcionario){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
            // Validaciones de persona
                $validatedPersona = $request->validate([
                'pers_nombre'   => 'required',
                'pers_apellido' => 'required',
                'pers_ci'       => 'required|unique:personas,pers_ci,' . $funcionario->persona->id,
                'pers_direc'    => 'required',
                'pers_telef'    => 'required',
                'pers_email'    => 'required|email|unique:personas,pers_email,' . $funcionario->persona->id,
                'pais_id'       => 'required',
                'ciudad_id'     => 'required'
            ]);
                // Validaciones de funcionario
            $validatedFuncionario = $request->validate([
                'func_fec_nac' => 'required|date',
                'func_fec_baja'=> 'nullable|date',
                'func_fec_ing' => 'required|date',
                'func_estado'  => 'required',
                'cargo_id'     => 'required',
                'user_id'      => 'required'
            ]);
            return DB::transaction(function () use ($funcionario, $validatedPersona, $validatedFuncionario) {
            // Actualizamos persona
            $funcionario->persona->update($validatedPersona);

            // Actualizamos funcionario
            $funcionario->update($validatedFuncionario);

            return response()->json([
                'mensaje'  => 'Registro actualizado con éxito',
                'tipo'     => 'success',
                'registro' => $funcionario->load('persona')
            ], 200);
        });
    }

    //en este modelo el destroy solo lo hace en funcionario pero persona no se borra ya que funcionario puede pasar a ser cliente
    public function destroy ($id){
        $funcionario = Funcionario::with('persona')->find($id);
            if(!$funcionario){
                return response()->json([
                    'mensaje'=> 'Registro no encontrado',
                    'tipo'=> 'error'
                ],404);
            }
            return DB::transaction(function () use ($funcionario) {
            // Borramos solo el funcionario
            $funcionario->delete();
            return response()->json([
                'mensaje'=> 'Registro de Funcionario eliminado con exito',
                'tipo'=>'success'
            ],200);
        });    
    }
    // Función para buscar funcionario
    public function buscar(Request $request){
        $query = $request->input('query'); // el frontend puede enviar "query" con nombre o apellido
        $funcionario = Funcionario::with('persona')
            ->whereHas('persona', function ($q) use ($query) {
                $q->where('pers_nombre', 'LIKE', '%' . $query . '%')
                  ->orWhere('pers_apellido', 'LIKE', '%' . $query . '%')
                  ->orWhere('pers_ci', 'ILIKE', "%{$query}%"); // búsqueda por CI
            })
            ->get();

        if($funcionario->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }
        return response()->json($funcionario, 200); // Retornar los resultados en formato JSON
    }
}
