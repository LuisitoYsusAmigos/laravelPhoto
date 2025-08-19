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
            'nombre' => 'required|string|max:255|unique:forma_de_pagos,nombre',
            'descripcion' => 'required|string|max:500',
            'activo' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 400);
        }

        $forma = FormaDePago::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->activo ?? true // Default true si no se envía
        ]);

        return response()->json([
            'message' => 'Forma de pago creada exitosamente',
            'data' => $forma
        ], 201);
    }

    // Obtener una forma de pago por su ID
    public function show($id)
    {
        $forma = FormaDePago::find($id);

        if (!$forma) {
            return response()->json([
                'message' => 'Forma de pago no encontrada'
            ], 404);
        }

        return response()->json([
            'data' => $forma
        ]);
    }

    // Actualizar una forma de pago
    public function update(Request $request, $id)
    {
        $forma = FormaDePago::find($id);

        if (!$forma) {
            return response()->json([
                'message' => 'Forma de pago no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:forma_de_pagos,nombre,' . $id,
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 400);
        }

        $forma->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->activo ?? $forma->activo // Mantiene el valor actual si no se envía
        ]);

        return response()->json([
            'message' => 'Forma de pago actualizada exitosamente',
            'data' => $forma
        ]);
    }

    // Eliminar una forma de pago (eliminación lógica cambiando activo a false)
    public function destroy($id)
    {
        $forma = FormaDePago::find($id);

        if (!$forma) {
            return response()->json([
                'message' => 'Forma de pago no encontrada'
            ], 404);
        }

        // Eliminación lógica - cambiar activo a false
        $forma->update(['activo' => false]);

        return response()->json([
            'message' => 'Forma de pago desactivada correctamente'
        ]);
    }

    // Método adicional para obtener solo las formas de pago activas
    public function activas()
    {
        $formas = FormaDePago::where('activo', true)->get();
        return response()->json([
            'data' => $formas
        ]);
    }

    // Método adicional para reactivar una forma de pago
    public function reactivar($id)
    {
        $forma = FormaDePago::find($id);

        if (!$forma) {
            return response()->json([
                'message' => 'Forma de pago no encontrada'
            ], 404);
        }

        $forma->update(['activo' => true]);

        return response()->json([
            'message' => 'Forma de pago reactivada correctamente',
            'data' => $forma
        ]);
    }
}