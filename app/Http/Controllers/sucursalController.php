<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Validator;

class SucursalController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::all();
        if ($sucursales->isEmpty()) {
            return response()->json(['message' => 'No hay sucursales registradas'], 404);
        }
        return response()->json($sucursales, 200);
    }

    public function store(Request $request)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'lugar' => 'required|unique:sucursal',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Faltan datos', 'errors' => $validator->errors()], 400);
        }

        // Crear sucursal
        $sucursal = Sucursal::create([
            'lugar' => $request->input('lugar'),
        ]);

        return response()->json(['message' => 'Sucursal creada', 'sucursal' => $sucursal], 201);
    }

    public function show($id)
    {
        $sucursal = Sucursal::find($id);
        if (empty($sucursal)) {
            return response()->json(['message' => 'No se encontró la sucursal'], 404);
        }
        return response()->json($sucursal, 200);
    }

    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::find($id);
        if (empty($sucursal)) {
            return response()->json(['message' => 'No se encontró la sucursal'], 404);
        }

        // Validación
        $validator = Validator::make($request->all(), [
            'lugar' => 'required|unique:sucursal,lugar,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Faltan datos', 'errors' => $validator->errors()], 400);
        }

        // Actualizar sucursal
        $sucursal->lugar = $request->input('lugar');
        $sucursal->save();

        return response()->json(['message' => 'Sucursal actualizada', 'sucursal' => $sucursal], 200);
    }

    public function destroy($id)
    {
        $sucursal = Sucursal::find($id);
        if (empty($sucursal)) {
            return response()->json(['message' => 'No se encontró la sucursal'], 404);
        }

        $sucursal->delete();
        return response()->json(['message' => 'Sucursal eliminada'], 200);
    }
}
