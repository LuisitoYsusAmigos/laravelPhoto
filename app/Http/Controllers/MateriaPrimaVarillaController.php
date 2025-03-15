<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MateriaPrimaVarilla;
use Illuminate\Support\Facades\Validator;

class MateriaPrimaVarillaController extends Controller
{
    // Obtener todas las varillas
    public function index()
    {
        $varillas = MateriaPrimaVarilla::all();
        return response()->json($varillas);
    }

    // Crear una nueva varilla
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string',
            'grosor' => 'required|integer',
            'ancho' => 'required|integer',
            'factor_desperdicio' => 'required|numeric|between:0,100',
            'categoria' => 'required|string',
            'sub_categoria' => 'required|string',
            'stock_global_actual' => 'required|integer',
            'stock_global_minimo' => 'required|integer',
            'id_sucursal' => 'required|exists:sucursal,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $varilla = MateriaPrimaVarilla::create($request->all());

        return response()->json($varilla, 201);
    }

    // Obtener una varilla por su ID
    public function show($id)
    {
        $varilla = MateriaPrimaVarilla::find($id);

        if (!$varilla) {
            return response()->json(['message' => 'Varilla no encontrada'], 404);
        }

        return response()->json($varilla);
    }

    // Actualizar una varilla
    public function update(Request $request, $id)
    {
        $varilla = MateriaPrimaVarilla::find($id);

        if (!$varilla) {
            return response()->json(['message' => 'Varilla no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'descripcion' => 'sometimes|string',
            'grosor' => 'sometimes|integer',
            'ancho' => 'sometimes|integer',
            'factor_desperdicio' => 'sometimes|numeric|between:0,100',
            'categoria' => 'sometimes|string',
            'sub_categoria' => 'sometimes|string',
            'stock_global_actual' => 'sometimes|integer',
            'stock_global_minimo' => 'sometimes|integer',
            'id_sucursal' => 'sometimes|exists:sucursal,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $varilla->update($request->all());

        return response()->json($varilla);
    }

    // Eliminar una varilla
    public function destroy($id)
    {
        $varilla = MateriaPrimaVarilla::find($id);

        if (!$varilla) {
            return response()->json(['message' => 'Varilla no encontrada'], 404);
        }

        $varilla->delete();

        return response()->json(['message' => 'Varilla eliminada correctamente']);
    }
}
