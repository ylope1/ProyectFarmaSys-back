<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Clientes;
use App\Models\Persona;
use App\Models\User;

class ClientesController extends Controller
{
    public function read(){
        return DB::select("select 
        cl.id,
        p.pers_nombre, 
        p.pers_apellido,
        p.pers_ci,
        p.pers_direc,
        p.pers_telef,
        p.pers_email,
        p.pais_id,
        p.ciudad_id,
        cl.cli_fec_nac,
        cl.cli_fec_baja,
        cl.cli_fec_ing,
        cl.cli_estado,
        ciu.ciudad_desc,
        pa.pais_desc
        from clientes cl
        join personas p on p.id = cl.persona_id
        join ciudades ciu on ciu.id = p.ciudad_id
        join paises pa on pa.id = p.pais_id; ");  
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
            $validatedClientes = $request->validate([
            'cli_fec_nac' => 'required',
            'cli_fec_baja' => 'nullable',
            'cli_fec_ing' => 'required',
            'cli_estado' => 'required',
            'cli_ruc' => 'nullable',
            'cli_linea_credito' => 'required'//pasar 0 si solo opera contado
        ]);

        return DB::transaction(function () use ($validatedPersona, $validatedClientes) {
            $persona = Persona::create($validatedPersona);

            $clientes = Clientes::create(array_merge($validatedClientes, [
                'persona_id' => $persona->id
            ]));
            return response()->json([
                'mensaje'=> 'Registro creado con exito',
                'tipo'=>'success',
                'registro'=> $clientes->load('persona')
            ],200);
        });
    }
    public function update(Request $request, $id){
        $clientes = Clientes::find($id);
        if(!$clientes){
            return response()->json([
                'mensaje'=> 'Registro no encontrado',
                'tipo'=> 'error'
            ],404);
        }
            // Validaciones de persona
                $validatedPersona = $request->validate([
                'pers_nombre'   => 'required',
                'pers_apellido' => 'required',
                'pers_ci'       => 'required|unique:personas,pers_ci,' . $clientes->persona->id,
                'pers_direc'    => 'required',
                'pers_telef'    => 'required',
                'pers_email'    => 'required|email|unique:personas,pers_email,' . $clientes->persona->id,
                'pais_id'       => 'required',
                'ciudad_id'     => 'required'
            ]);
                // Validaciones de funcionario
            $validatedClientes = $request->validate([
                'cli_fec_nac' => 'required',
                'cli_fec_baja' => 'nullable',
                'cli_fec_ing' => 'required',
                'cli_estado' => 'required',
                'cli_ruc' => 'nullable',
                'cli_linea_credito' => 'required'//pasar 0 si solo opera contado
            ]);
            return DB::transaction(function () use ($clientes, $validatedPersona, $validatedClientes) {
            // Actualizamos persona
            $clientes->persona->update($validatedPersona);

            // Actualizamos funcionario
            $clientes->update($validatedClientes);

            return response()->json([
                'mensaje'  => 'Registro actualizado con éxito',
                'tipo'     => 'success',
                'registro' => $clientes->load('persona')
            ], 200);
        });
    }

    //en este modelo el destroy solo lo hace en clientes pero persona no se borra ya que cliente puede pasar a ser funcionario o algo mas de la empresa
    public function destroy ($id){
        $clientes = Clientes::with('persona')->find($id);
            if(!$clientes){
                return response()->json([
                    'mensaje'=> 'Registro no encontrado',
                    'tipo'=> 'error'
                ],404);
            }
            return DB::transaction(function () use ($clientes) {
            // Borramos solo el cliente
            $clientes->delete();
            return response()->json([
                'mensaje'=> 'Registro de Cliente eliminado con exito',
                'tipo'=>'success'
            ],200);
        });    
    }
    // Función para buscar clientes con mi estilo de programación
    public function buscar(Request $request){
        return DB::select(" 
            select 
                c.id as cliente_id,
                p.pers_nombre||' '||p.pers_apellido as nombre_cliente, 
                p.pers_ci as cliente_ci, 
                c.cli_ruc
            from clientes c 
            join personas p on p.id = c.persona_id 
            where 
                (
                    p.pers_ci = ? 
                    OR c.cli_ruc ilike ?
                )
            and c.cli_estado = 'Activo'
            ", [
            $request->cliente_ci,
            '%' . $request->cliente_ci . '%'
        ]);
    }
}
