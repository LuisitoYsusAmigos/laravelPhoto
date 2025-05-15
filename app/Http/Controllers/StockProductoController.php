<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockProducto;
use Illuminate\Support\Facades\Validator;

class StockProductoController extends Controller
{
    // Obtener todos los registros de stock de productos
    public function index()
    {
        $stock = StockProducto::with('producto')->get();
        return response()->json($stock);
    }

    // Crear un nuevo registro de stock
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stock'           => 'required|integer|min:0',
            'precio'          => 'required|integer|min:0',
            'contable'        => 'required|boolean',
            'id_producto'     => 'required|exists:productos,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stockProducto = StockProducto::create($request->all());

        return response()->json($stockProducto, 201);
    }

    // Obtener un registro específico
    public function show($id)
    {
        $stockProducto = StockProducto::with('producto')->find($id);

        if (! $stockProducto) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json($stockProducto);
    }

    // Actualizar un registro existente
    public function update(Request $request, $id)
    {
        $stockProducto = StockProducto::find($id);

        if (! $stockProducto) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'stock'           => 'sometimes|required|integer|min:0',
            'precio'          => 'sometimes|required|integer|min:0',
            'contable'        => 'sometimes|required|boolean',
            'id_producto'     => 'sometimes|required|exists:productos,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $stockProducto->update($request->all());

        return response()->json($stockProducto);
    }

    // Eliminar un registro
    public function destroy($id)
    {
        $stockProducto = StockProducto::find($id);

        if (! $stockProducto) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $stockProducto->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }
}
