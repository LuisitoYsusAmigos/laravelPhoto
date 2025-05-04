<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    // Obtener todas las categorías
    public function index()
    {
        $categorias = Categoria::all();
        return response()->json($categorias);
    }
    // Obtener categorías filtradas por palabra en el campo 'tipo'
public function indexPorTipo($tipo)
{
    $categorias = Categoria::where('tipo', 'like', '%' . $tipo . '%')->get();

    if ($categorias->isEmpty()) {
        return response()->json(['message' => 'No se encontraron categorías con ese tipo'], 404);
    }

    return response()->json($categorias);
}


    // Crear una nueva categoría
    public function store(Request $request)
    {
        //dd($request->all());

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|unique:categorias,nombre',
            'tipo' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $categoria = Categoria::create([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo
        ]);

        return response()->json($categoria, 201);
    }

    // Obtener una categoría por su ID
    public function show($id)
    {
        $categoria = Categoria::find($id);
        
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        return response()->json($categoria);
    }

    // Actualizar una categoría
    public function update(Request $request, $id)
    {
        $categoria = Categoria::find($id);
        
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|unique:categorias,nombre,' . $id,
            'tipo' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $categoria->update($request->all());

        return response()->json($categoria);
    }

    // Eliminar una categoría
    public function destroy($id)
    {
        $categoria = Categoria::find($id);
        
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }
}
