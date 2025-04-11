<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MateriaPrimaTrupan;
use Illuminate\Support\Facades\Validator;

class MateriaPrimaTrupanController extends Controller
{
    // Obtener todos los trupanes
    public function index()
    {
        $trupanes = MateriaPrimaTrupan::all();
        return response()->json($trupanes);
    }

    // Crear un nuevo trupan
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

        $trupan = MateriaPrimaTrupan::create($request->all());

        return response()->json($trupan, 201);
    }

    // Obtener un trupan por su ID
    public function show($id)
    {
        $trupan = MateriaPrimaTrupan::find($id);

        if (!$trupan) {
            return response()->json(['message' => 'Trupan no encontrado'], 404);
        }

        return response()->json($trupan);
    }

    // Actualizar un trupan
    public function update(Request $request, $id)
    {
        $trupan = MateriaPrimaTrupan::find($id);

        if (!$trupan) {
            return response()->json(['message' => 'Trupan no encontrado'], 404);
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

        $trupan->update($request->all());

        return response()->json($trupan);
    }

    // Eliminar un trupan
    public function destroy($id)
    {
        $trupan = MateriaPrimaTrupan::find($id);

        if (!$trupan) {
            return response()->json(['message' => 'Trupan no encontrado'], 404);
        }

        $trupan->delete();

        return response()->json(['message' => 'Trupan eliminado correctamente']);
    }
    public function indexPaginado(Request $request)
    {
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $totalItems = MateriaPrimaTrupan::count();
        $totalPages = ceil($totalItems / $perPage);

        $trupanes = MateriaPrimaTrupan::skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'data' => $trupanes
        ]);
    }
    public function search(Request $request)
    {
        $searchTerm = $request->input('search', '');

        if (empty($searchTerm)) {
            return response()->json(MateriaPrimaTrupan::all());
        }

        $trupanes = MateriaPrimaTrupan::where('descripcion', 'LIKE', "%{$searchTerm}%")
            ->orWhere('categoria', 'LIKE', "%{$searchTerm}%")
            ->orWhere('sub_categoria', 'LIKE', "%{$searchTerm}%")
            ->get();

        if ($trupanes->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron coincidencias para: ' . $searchTerm,
                'data' => []
            ]);
        }

        return response()->json($trupanes);
    }
    public function searchCategorias(Request $request)
    {
        $categoria = $request->input('categoria');
        $subCategoria = $request->input('sub_categoria');
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $query = MateriaPrimaTrupan::query();

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        if ($subCategoria) {
            $query->where('sub_categoria', $subCategoria);
        }

        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        $trupanes = $query->skip(($page - 1) * $perPage)
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
            'data' => $trupanes
        ]);
    }
}
