<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockVarilla;
use Illuminate\Support\Facades\Validator;

class StockVarillaController extends Controller
{
    // Obtener todos los registros de stock de varillas
    public function index()
    {
        $stock = StockVarilla::with('materiaPrimaVarilla')->get();
        return response()->json($stock);
    }

    // Crear un nuevo registro de stock
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'largo' => 'required|integer|min:1',
            'precio' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'contable' => 'required|boolean',
            'id_materia_prima_varilla' => 'required|exists:materia_prima_varillas,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stockVarilla = StockVarilla::create($request->all());

        return response()->json($stockVarilla, 201);
    }

    // Obtener un registro específico
    public function show($id)
    {
        $stockVarilla = StockVarilla::with('materiaPrimaVarilla')->find($id);

        if (!$stockVarilla) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($stockVarilla);
    }

    // Actualizar un registro existente
    public function update(Request $request, $id)
    {
        
        $stockVarilla = StockVarilla::find($id);

        if (!$stockVarilla) {
            echo   "Registro no encontrado "+ $stockVarilla;
            return response()->json(['message' => 'Registro no encontrado',
        'estado'=> $stockVarilla], 404);
        }

        $validator = Validator::make($request->all(), [
            'largo' => 'sometimes|required|integer|min:1',
            'precio' => 'sometimes|required|integer|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'contable' => 'sometimes|required|boolean',
            'id_materia_prima_varilla' => 'sometimes|required|exists:materia_prima_varillas,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stockVarilla->update($request->all());

        return response()->json($stockVarilla);
    }

    // Eliminar un registro
    public function destroy($id)
    {
        $stockVarilla = StockVarilla::find($id);

        if (!$stockVarilla) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $stockVarilla->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }
}
