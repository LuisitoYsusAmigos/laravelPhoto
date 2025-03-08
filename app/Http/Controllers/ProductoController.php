<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    // Obtener todos los productos
    public function index()
    {
        $productos = Producto::all();
        return response()->json($productos);
    }

    // Crear un nuevo producto
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string',
            'precioCompra' => 'required|integer',
            'precioVenta' => 'required|integer',
            'stock' => 'required|integer',
            'stockMin' => 'required|integer',
            'actualizacion' => 'required|date',
            'sucursal_id' => 'required|exists:sucursal,id',
            'categoria_id' => 'required|exists:categorias,id',
            'sub_categoria_id' => 'required|exists:sub_categorias,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $producto = Producto::create($request->all());

        return response()->json($producto, 201);
    }

    // Obtener un producto por su ID
    public function show($id)
    {
        $producto = Producto::find($id);
        
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($producto);
    }

    // Actualizar un producto
    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string',
            'precioCompra' => 'required|integer',
            'precioVenta' => 'required|integer',
            'stock' => 'required|integer',
            'stockMin' => 'required|integer',
            'actualizacion' => 'required|date',
            'sucursal_id' => 'required|exists:sucursal,id',
            'categoria_id' => 'required|exists:categorias,id',
            'sub_categoria_id' => 'required|exists:sub_categorias,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $producto->update($request->all());

        return response()->json($producto);
    }

    // Eliminar un producto
    public function destroy($id)
    {
        $producto = Producto::find($id);
        
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}
