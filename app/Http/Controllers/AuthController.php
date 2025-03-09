<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'id_sucursal' => 'required|exists:sucursal,id' // Validar que exista la sucursal
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'id_sucursal' => $request->id_sucursal // Agregar sucursal al crear el usuario
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(Request $request)
    {
        // Validar que el request contenga username y password
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response([
                'message' => 'Usuario y contraseña son requeridos'
            ], 400);
        }
    
        // Verificar si el usuario existe
        $user = User::where('username', $request->username)->first();
        
        if (!$user) {
            // Si el usuario no existe
            return response([
                'message' => 'Usuario no existente'
            ], 404);
        }
    
        // Verificar si la contraseña es correcta
        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            // Si la contraseña es incorrecta
            return response([
                'message' => 'Contraseña incorrecta'
            ], 401);
        }
    
        // Si el login es exitoso
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Bienvenido ' . $user->name,
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
    

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
}
