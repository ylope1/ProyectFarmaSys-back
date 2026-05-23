<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Perfil;
use \stdClass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

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
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'DEBE INGRESAR USUARIO Y CONTRASEÑA'
            ], 422);
        }

        $user = User::where('login', $request->login)->first();

        if ($user == null) {
            return response()->json([
                'message' => 'USUARIO O CONTRASEÑA INCORRECTA'
            ], 401);
        }

        // Si ya tiene 3 intentos fallidos, queda bloqueado
        if ($user->intentos >= 3) {
            return response()->json([
                'message' => 'USUARIO BLOQUEADO POR INTENTOS FALLIDOS'
            ], 401);
        }

        // Si la contraseña es incorrecta, suma un intento
        if (!Hash::check($request->password, $user->password)) {
            $user->intentos = $user->intentos + 1;
            $user->save();

            if ($user->intentos >= 3) {
                return response()->json([
                    'message' => 'USUARIO BLOQUEADO POR INTENTOS FALLIDOS'
                ], 401);
            }

            return response()->json([
                'message' => 'USUARIO O CONTRASEÑA INCORRECTA'
            ], 401);
        }

        $perfil = Perfil::where('id', $user->perfil_id)->firstOrFail();

        $user->intentos = 0;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Bienvenido '.$user->name,
            'accessToken' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'perfil' => $perfil
        ], 200);
    }

    public function logout()
    {
        if (auth()->check()) {
            auth()->user()->tokens()->delete();
            return ['message' => 'Usted se ha desconectado satisfactoriamente'];
        }
        return ['message' => 'No hay usuario autenticado para desconectar'];
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'DEBE INGRESAR SU USUARIO O CORREO ELECTRÓNICO'
            ], 422);
        }

        $user = User::where('login', $request->usuario)
            ->orWhere('email', $request->usuario)
            ->first();

        if ($user == null) {
            return response()->json([
                'message' => 'NO SE ENCONTRÓ UN USUARIO CON LOS DATOS INGRESADOS'
            ], 404);
        }

        if ($user->email == null || $user->email == '') {
            return response()->json([
                'message' => 'EL USUARIO NO TIENE UN CORREO ELECTRÓNICO REGISTRADO'
            ], 422);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );
        //aca tengo que colocar la pagina de reset password del frontend, con el token y el email como parametros
        $link = 'http://localhost/ProyectFarmaSys-front/reset-password.html?token='.$token.'&email='.$user->email;

        Mail::raw("Hola {$user->name},\n\nPara restablecer tu contraseña ingresa al siguiente enlace:\n\n{$link}\n\nSi no solicitaste este cambio, ignora este mensaje.", function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Recuperación de contraseña - FarmaSys');
        });

        return response()->json([
            'message' => 'SE ENVIÓ UN ENLACE DE RECUPERACIÓN AL CORREO REGISTRADO'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:3|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'LOS DATOS INGRESADOS NO SON VÁLIDOS',
                'errors' => $validator->errors()
            ], 422);
        }

        $registro = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if ($registro == null) {
            return response()->json([
                'message' => 'EL TOKEN DE RECUPERACIÓN NO EXISTE O YA FUE UTILIZADO'
            ], 404);
        }

        if (!Hash::check($request->token, $registro->token)) {
            return response()->json([
                'message' => 'EL TOKEN DE RECUPERACIÓN NO ES VÁLIDO'
            ], 401);
        }

        $fechaToken = Carbon::parse($registro->created_at);

        if ($fechaToken->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return response()->json([
                'message' => 'EL TOKEN DE RECUPERACIÓN HA EXPIRADO'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        if ($user == null) {
            return response()->json([
                'message' => 'USUARIO NO ENCONTRADO'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->intentos = 0;
        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'message' => 'CONTRASEÑA ACTUALIZADA CORRECTAMENTE'
        ], 200);
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
