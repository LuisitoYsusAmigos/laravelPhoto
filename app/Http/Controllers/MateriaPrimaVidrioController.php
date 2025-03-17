<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MateriaPrimaVidrio;
use Illuminate\Support\Facades\Validator;

class MateriaPrimaVidrioController extends Controller
{
    // Obtener todos los vidrios
    public function index()
    {
        $vidrios = MateriaPrimaVidrio::all();
        return response()->json($vidrios);
    }

    // Crear un nuevo vidrio
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string',
            'grosor' => 'required|integer|min:1',
            'factor_desperdicio' => 'required|numeric|min:0|max:100',
            'categoria' => 'required|string',
            'sub_categoria' => 'required|string',
            'stock_global_actual' => 'required|integer|min:0',
            'stock_global_minimo' => 'required|integer|min:0',
            'id_sucursal' => 'required|exists:sucursal,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $vidrio = MateriaPrimaVidrio::create($request->all());

        return response()->json($vidrio, 201);
    }

    // Obtener un vidrio por su ID
    public function show($id)
    {
        $vidrio = MateriaPrimaVidrio::find($id);
        
        if (!$vidrio) {
            return response()->json(['message' => 'Vidrio no encontrado'], 404);
        }

        return response()->json($vidrio);
    }

    // Actualizar un vidrio
    public function update(Request $request, $id)
    {
        $vidrio = MateriaPrimaVidrio::find($id);

        if (!$vidrio) {
            return response()->json(['message' => 'Vidrio no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'descripcion' => 'sometimes|string',
            'grosor' => 'sometimes|integer|min:1',
            'factor_desperdicio' => 'sometimes|numeric|min:0|max:100',
            'categoria' => 'sometimes|string',
            'sub_categoria' => 'sometimes|string',
            'stock_global_actual' => 'sometimes|integer|min:0',
            'stock_global_minimo' => 'sometimes|integer|min:0',
            'id_sucursal' => 'sometimes|exists:sucursal,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $vidrio->update($request->all());

        return response()->json($vidrio);
    }

    // Eliminar un vidrio
    public function destroy($id)
    {
        $vidrio = MateriaPrimaVidrio::find($id);

        if (!$vidrio) {
            return response()->json(['message' => 'Vidrio no encontrado'], 404);
        }

        $vidrio->delete();

        return response()->json(['message' => 'Vidrio eliminado correctamente']);
    }
}
