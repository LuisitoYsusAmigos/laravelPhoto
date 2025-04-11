<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ProductoController extends Controller
{
    // Obtener todos los productos
    public function indexPaginado(Request $request)
{
    // Obtener parámetros de paginación con valores por defecto
    $page = (int) $request->input('page', 1);
    $perPage = (int) $request->input('perPage', 10);

    // Obtener el total de registros
    $totalItems = Producto::count();

    // Calcular el total de páginas
    $totalPages = $perPage > 0 ? ceil($totalItems / $perPage) : 1;

    // Obtener los productos paginados
    $productos = Producto::skip(($page - 1) * $perPage)
                         ->take($perPage)
                         ->get();

    // Devolver la respuesta con el formato solicitado
    return response()->json([
        'currentPage' => $page,
        'perPage' => $perPage,
        'totalItems' => $totalItems,
        'totalPages' => $totalPages,
        'data' => $productos
    ]);
}

    public function index()
    {
        $productos = Producto::all();
        return response()->json($productos);
    }

    // Crear un nuevo producto
    public function store(Request $request)
    {
        Log::info('📥 Nueva solicitud para crear un producto.');

        // Validar los datos del request
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
            Log::error('❌ Error de validación', ['errors' => $validator->errors()]);
            return response()->json($validator->errors(), 400);
        }

        // Crear el producto sin imagen para obtener el ID
        $producto = Producto::create($request->except('imagen'));

        // Verificar si se subió una imagen
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');

            if (!$file->isValid()) {
                Log::error('❌ La imagen subida no es válida.');
                return response()->json(['error' => 'Archivo de imagen no válido'], 400);
            }

            Log::info('✅ Imagen recibida', [
                'nombre_original' => $file->getClientOriginalName(),
                'tamaño' => $file->getSize(),
                'tipo' => $file->getMimeType()
            ]);

            // Definir la ruta de almacenamiento en public/storage/productos
            $destinationPath = public_path('storage/productos');

            // Asegurar que la carpeta existe
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Crear el nombre de archivo con el ID del producto
            $extension = $file->getClientOriginalExtension();
            $filename = $producto->id . '.' . $extension;

            // Mover la imagen a la carpeta deseada
            $file->move($destinationPath, $filename);

            // Guardar la ruta relativa en la base de datos
            $producto->imagen = 'storage/productos/' . $filename;
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
            if ($producto->imagen && File::exists(public_path($producto->imagen))) {
                File::delete(public_path($producto->imagen));
            }

            $file = $request->file('imagen');
            $extension = $file->getClientOriginalExtension();
            $filename = $producto->id . '.' . $extension;

            $destinationPath = public_path('storage/productos');
            $file->move($destinationPath, $filename);

            $producto->imagen = 'storage/productos/' . $filename;
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
        if ($producto->imagen && File::exists(public_path($producto->imagen))) {
            File::delete(public_path($producto->imagen));
        }

        $producto->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
    public function search(Request $request)
    {
        $searchTerm = $request->input('search', '');
    
        if (empty($searchTerm)) {
            return response()->json([
                'message' => 'Debe ingresar un término de búsqueda.',
                'data' => []
            ], 400);
        }
    
        $productos = Producto::where('descripcion', 'LIKE', "%{$searchTerm}%")->get();
    
        if ($productos->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron coincidencias para: ' . $searchTerm,
                'data' => []
            ]);
        }
    
        return response()->json($productos);
    }

    public function searchCategorias(Request $request)
{
    $categoriaId = $request->input('categoria_id');
    $subCategoriaId = $request->input('sub_categoria_id');
    $page = $request->input('page', 1);
    $perPage = $request->input('perPage', 10);

    $query = Producto::query();

    if ($categoriaId) {
        $query->where('categoria_id', $categoriaId);
    }

    if ($subCategoriaId) {
        $query->where('sub_categoria_id', $subCategoriaId);
    }

    $totalItems = $query->count();
    $totalPages = ceil($totalItems / $perPage);

    $productos = $query->skip(($page - 1) * $perPage)
                       ->take($perPage)
                       ->get();

    return response()->json([
        'currentPage' => (int)$page,
        'perPage' => (int)$perPage,
        'totalItems' => $totalItems,
        'totalPages' => $totalPages,
        'data' => $productos
    ]);
}

    

}
