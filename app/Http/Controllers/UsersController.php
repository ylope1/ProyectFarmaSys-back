<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                u.id,
                u.name,
                u.email,
                u.login,
                u.intentos,
                u.rol_id,
                r.rol_desc,
                u.user_estado,
                to_char(u.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at,
                to_char(u.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at
            FROM users u
            LEFT JOIN roles r ON r.id = u.rol_id
            ORDER BY u.id ASC
        ");
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:3',
            'login' => 'required|string|max:100|unique:users,login',
            'rol_id' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Datos inválidos',
                'tipo' => 'error',
                'errores' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            DB::table('users')->insert([
                'name' => strtoupper($request->name),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'login' => $request->login,
                'intentos' => 0,
                'rol_id' => $request->rol_id,
                'user_estado' => strtoupper($request->user_estado),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Usuario registrado correctamente',
                'tipo' => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al registrar usuario',
                'tipo' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $usuario = DB::selectOne("
            SELECT id
            FROM users
            WHERE id = ?
        ", [$id]);

        if (!$usuario) {
            return response()->json([
                'mensaje' => 'El usuario no existe',
                'tipo' => 'error'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'login' => 'required|string|max:100|unique:users,login,' . $id,
            'rol_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Datos inválidos',
                'tipo' => 'error',
                'errores' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $datos = [
                'name' => strtoupper($request->name),
                'email' => $request->email,
                'login' => $request->login,
                'rol_id' => $request->rol_id,
                'updated_at' => now()
            ];

            if ($request->password != null && trim($request->password) != '') {
                $datos['password'] = Hash::make($request->password);
                $datos['intentos'] = 0;
            }

            DB::table('users')
                ->where('id', $id)
                ->update($datos);

            DB::commit();

            return response()->json([
                'mensaje' => 'Usuario actualizado correctamente',
                'tipo' => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'mensaje' => 'Error al actualizar usuario',
                'tipo' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function anular($id)
    {
        $usuario = DB::selectOne("
            SELECT id, user_estado
            FROM users
            WHERE id = ?
        ", [$id]);

        if (!$usuario) {
            return response()->json([
                'mensaje' => 'El usuario no existe',
                'tipo' => 'error'
            ], 404);
        }

        DB::table('users')
            ->where('id', $id)
            ->update([
                'user_estado' => 'INACTIVO',
                'updated_at' => now()
            ]);

        return response()->json([
            'mensaje' => 'Usuario inactivado correctamente',
            'tipo' => 'success'
        ], 200);
    }

    public function desbloquear($id)
    {
        $usuario = DB::selectOne("
            SELECT id
            FROM users
            WHERE id = ?
        ", [$id]);

        if (!$usuario) {
            return response()->json([
                'mensaje' => 'El usuario no existe',
                'tipo' => 'error'
            ], 404);
        }

        DB::table('users')
            ->where('id', $id)
            ->update([
                'intentos' => 0,
                'user_estado' => 'ACTIVO',
                'updated_at' => now()
            ]);

        return response()->json([
            'mensaje' => 'Usuario desbloqueado correctamente',
            'tipo' => 'success'
        ], 200);
    }

    // Función para buscar users por login
    public function buscar(Request $request){
        $query = $request->input('login');

        $user = User::where('login', 'LIKE', "%{$query}%")->get();

        if($user->isEmpty()){
            return response()->json([
                'mensaje' => 'No se encontraron resultados',
                'tipo' => 'error'
            ], 404);
        }

        return response()->json($user, 200);
    }
}
