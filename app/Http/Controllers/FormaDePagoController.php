<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormaDePago;
use Illuminate\Support\Facades\Validator;

class FormaDePagoController extends Controller
{
    // Obtener todas las formas de pago
    public function index()
    {
        $formas = FormaDePago::all();
        return response()->json($formas);
    }

    // Crear una nueva forma de pago
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|unique:forma_de_pagos,nombre',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $forma = FormaDePago::create([
            'nombre' => $request->nombre,
        ]);

        return response()->json($forma, 201);
    }

    // Obtener una forma de pago por su ID
    public function show($id)
    {
        $forma = FormaDePago::find($id);

        if (!$forma) {
            return response()->json(['message' => 'Forma de pago no encontrada'], 404);
        }

        return response()->json($forma);
    }

    // Actualizar una forma de pago
    public function update(Request $request, $id)
    {
        $forma = FormaDePago::find($id);

        if (!$forma) {
            return response()->json(['message' => 'Forma de pago no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|unique:forma_de_pagos,nombre,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $forma->update($request->all());

        return response()->json($forma);
    }

    // Eliminar una forma de pago
    public function destroy($id)
    {
        $forma = FormaDePago::find($id);

        if (!$forma) {
            return response()->json(['message' => 'Forma de pago no encontrada'], 404);
        }

        $forma->delete();

        return response()->json(['message' => 'Forma de pago eliminada correctamente']);
    }
}
    