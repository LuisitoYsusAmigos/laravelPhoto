<?php

namespace App\Http\Controllers;

use App\Models\StockContorno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockContornoController extends Controller
{
    // Obtener todos los registros de stock de contornos
    public function index()
    {
        $stockContornos = StockContorno::with('materiaPrimaContorno')->get();
        return response()->json($stockContornos);
    }

    // Crear un nuevo registro de stock
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'largo' => 'required|integer|min:1',
            'alto' => 'required|integer|min:1',
            'stock' => 'required|integer|min:0',
            'precio' => 'required|integer|min:0',
            'contable' => 'required|boolean',
            'id_materia_prima_contorno' => 'required|exists:materia_prima_contornos,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stockContorno = StockContorno::create($request->all());

        return response()->json($stockContorno, 201);
    }

    // Obtener un registro específico
    public function show($id)
    {
        $stockContorno = StockContorno::find($id);

        if (!$stockContorno) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($stockContorno);
    }

    // Actualizar un registro existente
    public function update(Request $request, $id)
    {
        $stockContorno = StockContorno::find($id);

        if (!$stockContorno) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'largo' => 'sometimes|required|integer|min:1',
            'alto' => 'sometimes|required|integer|min:1',
            'stock' => 'sometimes|required|integer|min:0',
            'precio' => 'sometimes|required|integer|min:0',
            'contable' => 'sometimes|required|boolean',
            'id_materia_prima_contorno' => 'sometimes|required|exists:materia_prima_contornos,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stockContorno->update($request->all());

        return response()->json($stockContorno);
    }

    // Eliminar un registro
    public function destroy($id)
    {
        $stockContorno = StockContorno::find($id);

        if (!$stockContorno) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $stockContorno->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    // Obtener stock por contorno
    public function indexPorContorno($id)
    {
        $stock = StockContorno::where('id_materia_prima_contorno', $id)->get();
        return response()->json($stock);
    }
}
