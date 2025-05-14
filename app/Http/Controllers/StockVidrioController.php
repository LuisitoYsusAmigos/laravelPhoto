<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockVidrio;
use Illuminate\Support\Facades\Validator;

class StockVidrioController extends Controller
{
    // Obtener todos los registros de stock de vidrios
    public function index()
    {
        $stockVidrios = StockVidrio::with('materiaPrimaVidrio')->get();
        return response()->json($stockVidrios);
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
            'id_materia_prima_vidrio' => 'required|exists:materia_prima_vidrios,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stockVidrio = StockVidrio::create($request->all());

        return response()->json($stockVidrio, 201);
    }

    // Obtener un registro específico
    public function show($id)
    {
       // $stockVidrio = StockVidrio::with('materiaPrimaVidrio')->find($id);
        $stockVidrio = StockVidrio::find($id);
        if (!$stockVidrio) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($stockVidrio);
    }

    // Actualizar un registro existente
    public function update(Request $request, $id)
    {
        $stockVidrio = StockVidrio::find($id);

        if (!$stockVidrio) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'largo' => 'sometimes|required|integer|min:1',
            'alto' => 'sometimes|required|integer|min:1',
            'stock' => 'sometimes|required|integer|min:0',
            'precio' => 'sometimes|required|integer|min:0',
            'contable' => 'sometimes|required|boolean',
            'id_materia_prima_vidrio' => 'sometimes|required|exists:materia_prima_vidrios,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stockVidrio->update($request->all());

        return response()->json($stockVidrio);
    }

    // Eliminar un registro
    public function destroy($id)
    {
        $stockVidrio = StockVidrio::find($id);

        if (!$stockVidrio) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $stockVidrio->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }
}
