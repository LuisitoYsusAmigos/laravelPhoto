<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTrupan;
use Illuminate\Support\Facades\Validator;

class StockTrupanController extends Controller
{
    public function index()
    {
    $stock = StockTrupan::with('materiaPrimaTrupan')->get();
    return response()->json($stock);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alto' => 'required|integer|min:1',
            'largo' => 'required|integer|min:1',
            'precio' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'contable' => 'required|boolean',
            'id_materia_prima_trupans' => 'required|exists:materia_prima_trupans,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stock = StockTrupan::create($request->all());

        return response()->json($stock, 201);
    }

public function show($id)
{
    $stock = StockTrupan::with('materiaPrimaTrupan')->find($id);

    if (!$stock) {
        return response()->json(['message' => 'Registro no encontrado'], 404);
    }

    return response()->json($stock);
}

    public function update(Request $request, $id)
    {
        $stock = StockTrupan::find($id);

        if (!$stock) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'largo' => 'sometimes|required|integer|min:1',
            'precio' => 'sometimes|required|integer|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'contable' => 'sometimes|required|boolean',
            'id_materia_prima_trupan' => 'sometimes|required|exists:materia_prima_trupans,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stock->update($request->all());

        return response()->json($stock);
    }

    public function destroy($id)
    {
        $stock = StockTrupan::find($id);

        if (!$stock) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $stock->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }
}
