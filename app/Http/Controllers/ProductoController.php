<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
            'sub_categoria_id' => 'required|exists:sub_categorias,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear el producto sin imagen para obtener el ID
        $producto = Producto::create($request->except('imagen'));

       
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $extension = $file->getClientOriginalExtension(); // Obtener la extensión
            $filename = $producto->id . '.' . $extension; // Nombre basado en el ID

            // Guardar la imagen en 'storage/app/public/productos/'
            $path = $file->storeAs('productos', $filename, 'public');

            // Guardar la ruta en la base de datos
            $producto->imagen = $path;
            $producto->save();
        }

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

    // Actualizar un producto (cambia la imagen si se sube una nueva)
    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'descripcion' => 'sometimes|string',
            'precioCompra' => 'sometimes|integer',
            'precioVenta' => 'sometimes|integer',
            'stock' => 'sometimes|integer',
            'stockMin' => 'sometimes|integer',
            'actualizacion' => 'sometimes|date',
            'sucursal_id' => 'sometimes|exists:sucursal,id',
            'categoria_id' => 'sometimes|exists:categorias,id',
            'sub_categoria_id' => 'sometimes|exists:sub_categorias,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Actualizar datos del producto (excepto imagen)
        $producto->fill($request->except('imagen'));

        // Si se sube una nueva imagen, eliminar la anterior y guardar la nueva con el ID
        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si existe
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }

            $file = $request->file('imagen');
            $extension = $file->getClientOriginalExtension();
            $filename = $producto->id . '.' . $extension; // Nombre con ID

            // Guardar la imagen con el nuevo nombre en 'storage/app/public/productos/'
            $path = $file->storeAs('productos', $filename, 'public');

            // Guardar la ruta en la base de datos
            $producto->imagen = $path;
        }

        $producto->save();

        return response()->json($producto);
    }

    // Eliminar un producto (también borra la imagen del almacenamiento)
    public function destroy($id)
    {
        $producto = Producto::find($id);
        
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Eliminar la imagen si existe
        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}
