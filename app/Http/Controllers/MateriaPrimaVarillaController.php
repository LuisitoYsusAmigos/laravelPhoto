<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MateriaPrimaVarilla;
use Illuminate\Support\Facades\Validator;

class MateriaPrimaVarillaController extends Controller
{
    // Obtener todas las varillas
    public function index()
    {
        $varillas = MateriaPrimaVarilla::all();
        return response()->json($varillas);
    }

    // Crear una nueva varilla
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string',
            'grosor' => 'required|integer',
            'ancho' => 'required|integer',
            'factor_desperdicio' => 'required|numeric|between:0,100',
            'categoria' => 'required|string',
            'sub_categoria' => 'required|string',
            'stock_global_actual' => 'required|integer',
            'stock_global_minimo' => 'required|integer',
            'id_sucursal' => 'required|exists:sucursal,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $varilla = MateriaPrimaVarilla::create($request->all());

        return response()->json($varilla, 201);
    }

    // Obtener una varilla por su ID
    public function show($id)
    {
        $varilla = MateriaPrimaVarilla::find($id);

        if (!$varilla) {
            return response()->json(['message' => 'Varilla no encontrada'], 404);
        }

        return response()->json($varilla);
    }

    // Actualizar una varilla
    public function update(Request $request, $id)
    {
        $varilla = MateriaPrimaVarilla::find($id);

        if (!$varilla) {
            return response()->json(['message' => 'Varilla no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'descripcion' => 'sometimes|string',
            'grosor' => 'sometimes|integer',
            'ancho' => 'sometimes|integer',
            'factor_desperdicio' => 'sometimes|numeric|between:0,100',
            'categoria' => 'sometimes|string',
            'sub_categoria' => 'sometimes|string',
            'stock_global_actual' => 'sometimes|integer',
            'stock_global_minimo' => 'sometimes|integer',
            'id_sucursal' => 'sometimes|exists:sucursal,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $varilla->update($request->all());

        return response()->json($varilla);
    }

    // Eliminar una varilla
    public function destroy($id)
    {
        $varilla = MateriaPrimaVarilla::find($id);

        if (!$varilla) {
            return response()->json(['message' => 'Varilla no encontrada'], 404);
        }

        $varilla->delete();

        return response()->json(['message' => 'Varilla eliminada correctamente']);
    }

    public function indexPaginado(Request $request)
    {
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $totalItems = MateriaPrimaVarilla::count();
        $totalPages = ceil($totalItems / $perPage);

        $varillas = MateriaPrimaVarilla::skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'data' => $varillas
        ]);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search', '');

        if (empty($searchTerm)) {
            return response()->json(MateriaPrimaVarilla::all());
        }

        $varillas = MateriaPrimaVarilla::where('descripcion', 'LIKE', "%{$searchTerm}%")
            ->orWhere('categoria', 'LIKE', "%{$searchTerm}%")
            ->orWhere('sub_categoria', 'LIKE', "%{$searchTerm}%")
            ->get();

        if ($varillas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron coincidencias para: ' . $searchTerm,
                'data' => []
            ]);
        }

        return response()->json($varillas);
    }

    public function searchCategorias(Request $request)
    {
        $categoria = $request->input('categoria');
        $subCategoria = $request->input('sub_categoria');
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $query = MateriaPrimaVarilla::query();

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        if ($subCategoria) {
            $query->where('sub_categoria', $subCategoria);
        }

        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        $varillas = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'filters' => [
                'categoria' => $categoria,
                'sub_categoria' => $subCategoria
            ],
            'data' => $varillas
        ]);
    }
}
