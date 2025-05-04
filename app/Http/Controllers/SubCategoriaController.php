<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubCategoria;
use App\Models\Categoria;
use Illuminate\Support\Facades\Validator;

class SubCategoriaController extends Controller
{
    // Obtener todas las subcategorías
    public function index()
    {
        $subcategorias = SubCategoria::all();
        return response()->json($subcategorias);
    }

    // Crear una nueva subcategoría
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|unique:sub_categorias,nombre',
            'id_categoria' => 'required|exists:categorias,id' // Validar que exista la categoría
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subcategoria = SubCategoria::create([
            'nombre' => $request->nombre,
            'id_categoria' => $request->id_categoria // Agregar categoría al crear la subcategoría
        ]);

        return response()->json($subcategoria, 201);
    }

    // Obtener una subcategoría por su ID
    public function show($id)
    {
        $subcategoria = SubCategoria::find($id);
        
        if (!$subcategoria) {
            return response()->json(['message' => 'Subcategoría no encontrada'], 404);
        }

        return response()->json($subcategoria);
    }

    // Actualizar una subcategoría
    public function update(Request $request, $id)
    {
        $subcategoria = SubCategoria::find($id);
        
        if (!$subcategoria) {
            return response()->json(['message' => 'Subcategoría no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|unique:sub_categorias,nombre,' . $id,
            'id_categoria' => 'required|exists:categorias,id' // Validar que exista la categoría
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $subcategoria->update($request->all());

        return response()->json($subcategoria);
    }

    // Eliminar una subcategoría
    public function destroy($id)
    {
        $subcategoria = SubCategoria::find($id);
        
        if (!$subcategoria) {
            return response()->json(['message' => 'Subcategoría no encontrada'], 404);
        }

        $subcategoria->delete();

        return response()->json(['message' => 'Subcategoría eliminada correctamente']);
    }
    public function subcategoriasPorCategoria($id_categoria)
{
    // Verificar si la categoría existe
    $categoria = Categoria::find($id_categoria);
    if (!$categoria) {
        return response()->json(['message' => 'Categoría no encontrada'], 404);
    }

    // Obtener las subcategorías asociadas
    $subcategorias = SubCategoria::where('id_categoria', $id_categoria)->get();

    return response()->json($subcategorias);
}
}
