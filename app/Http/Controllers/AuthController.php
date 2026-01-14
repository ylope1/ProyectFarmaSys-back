<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Perfil;
use \stdClass;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'string|email|max:255|unique:users',
            'password' => 'required|string|min:3',
            'login' => 'required|string',
            'perfil_id' => 'required|exists:perfiles,id'
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'login' => $request->login,
            'perfil_id' => $request->perfil_id
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['data' => $user,'access_token' => $token, 'token_type' => 'Bearer',]);
    }

    public function login(Request $request)
    {
        if(!Auth::attempt($request->only('login','password'))){
            $user = User::where('login',$request['login'])->first();
            if($user!=null){
                $user->intentos = $user->intentos + 1;
                $user->save();
            }
            return response()->json(['message' => 'USUARIO O CONTRASEÑA INCORRECTA'],401);
        }

        $user = User::where('login',$request['login'])->firstOrFail();
        $perfil = Perfil::where('id',$user->perfil_id)->firstOrFail();
        if($user->intentos > 2){
            return response()->json(['message' => 'USUARIO BLOQUEADO POR INTENTOS FALLIDOS'],401);
        }
        $user->intentos = 0;
        $user->save();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json([
                'message' => 'Bienvenido '.$user->name,
                'accessToken' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'perfil' => $perfil
            ]);
    }

    public function logout()
    {
        if (auth()->check()) {
            auth()->user()->tokens()->delete();
            return ['message' => 'Usted se ha desconectado satisfactoriamente'];
        }
        return ['message' => 'No hay usuario autenticado para desconectar'];
    }
    // Función para buscar users
    public function buscar(Request $request){
        $query = $request->input('login'); // Obtener el valor de 'login' del frontend
        $user = User::where('login', 'LIKE', "%{$query}%")->get(); // Filtrar login por el nombre

        if($user->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }

        return response()->json($user, 200); // Retornar los resultados en formato JSON
    }
}
