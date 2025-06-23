<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lugar;
use Illuminate\Support\Facades\Validator;

class LugarController extends Controller
{
    // Obtener todos los lugares
    public function index()
    {
        $lugares = Lugar::all();
        return response()->json($lugares);
    }

    // Crear un nuevo lugar
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|unique:lugars,nombre'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $lugar = Lugar::create([
            'nombre' => $request->nombre
        ]);

        return response()->json($lugar, 201);
    }

    // Obtener un lugar por su ID
    public function show($id)
    {
        $lugar = Lugar::find($id);

        if (!$lugar) {
            return response()->json(['message' => 'Lugar no encontrado'], 404);
        }

        return response()->json($lugar);
    }

    // Actualizar un lugar
    public function update(Request $request, $id)
    {
        $lugar = Lugar::find($id);

        if (!$lugar) {
            return response()->json(['message' => 'Lugar no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|unique:lugars,nombre,' . $id
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $lugar->update($request->only('nombre'));

        return response()->json($lugar);
    }

    // Eliminar un lugar
    public function destroy($id)
    {
        $lugar = Lugar::find($id);

        if (!$lugar) {
            return response()->json(['message' => 'Lugar no encontrado'], 404);
        }

        $lugar->delete();

        return response()->json(['message' => 'Lugar eliminado correctamente']);
    }
}
