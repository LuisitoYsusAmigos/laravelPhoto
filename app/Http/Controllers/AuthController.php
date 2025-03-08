<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator; // Corrección aquí
use App\Models\User;
use \stdClass;


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validación con username en lugar de solo email
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username', // Se agrega username
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        if($validator->fails()){
            return response($validator->errors(), 400);
        }

        // Crear un nuevo usuario
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username, // Se agrega username
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        // Generar un token para el usuario
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data'=> $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(Request $request)
    {
        // Intentar autenticación con username en lugar de email
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('username', $request['username'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Hi ' . $user->name,
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
