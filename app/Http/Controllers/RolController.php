<?php

namespace App\Http\Controllers;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::all();
        if ($roles->isEmpty()) {
            return response()->json(['message' => 'No hay roles registrados'], 404);
        }
        return response()->json($roles, 200);
    }

    public function store(Request $request)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|unique:roles',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Faltan datos', 'errors' => $validator->errors()], 400);
        }

        // Crear rol
        $rol = Rol::create([
            'nombre' => $request->input('nombre'),
        ]);

        return response()->json(['message' => 'Rol creado', 'rol' => $rol], 201);
    }

    public function show($id)
    {
        $rol = Rol::find($id);
        if (empty($rol)) {
            return response()->json(['message' => 'No se encontró el rol'], 404);
        }
        return response()->json($rol, 200);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::find($id);
        if (empty($rol)) {
            return response()->json(['message' => 'No se encontró el rol'], 404);
        }

        // Validación
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|unique:roles,nombre,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Faltan datos', 'errors' => $validator->errors()], 400);
        }

        // Actualizar rol
        $rol->nombre = $request->input('nombre');
        $rol->save();

        return response()->json(['message' => 'Rol actualizado', 'rol' => $rol], 200);
    }

    public function destroy($id)
    {
        $rol = Rol::find($id);
        if (empty($rol)) {
            return response()->json(['message' => 'No se encontró el rol'], 404);
        }

        $rol->delete();
        return response()->json(['message' => 'Rol eliminado'], 200);
    }
} 