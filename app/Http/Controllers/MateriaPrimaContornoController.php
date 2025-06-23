<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MateriaPrimaContorno;
use Illuminate\Support\Facades\Validator;

class MateriaPrimaContornoController extends Controller
{
    public function index()
    {
        return response()->json(MateriaPrimaContorno::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'string|nullable',
            'descripcion' => 'required|string',
            'precioCompra' => 'required|numeric|min:0',
            'precioVenta' => 'required|numeric|min:0',
            'alto' => 'required|integer|min:1',
            'largo' => 'required|integer|min:1',
            'factor_desperdicio' => 'required|numeric|min:0|max:100',
            'categoria_id' => 'required|exists:categorias,id',
            'id_lugar' => 'required|exists:lugars,id',
            'sub_categoria_id' => 'nullable|exists:sub_categorias,id',
            'stock_global_actual' => 'required|integer|min:0',
            'stock_global_minimo' => 'required|integer|min:0',
            'id_sucursal' => 'required|exists:sucursal,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->except('imagen', 'stock_global_actual');
        $data['stock_global_actual'] = 0;

        $contorno = MateriaPrimaContorno::create($request->except('imagen'));

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = $contorno->id . '.' . $file->getClientOriginalExtension();
            $path = public_path('storage/materias_primas_contorno');

            if (!file_exists($path)) mkdir($path, 0777, true);

            $file->move($path, $filename);
            $contorno->imagen = 'storage/materias_primas_contorno/' . $filename;

            $contorno->save();
        }

        return response()->json($contorno, 201);
    }

    public function show($id)
    {
        $contorno = MateriaPrimaContorno::find($id);

        if (!$contorno) {
            return response()->json(['message' => 'Contorno no encontrado'], 404);
        }

        return response()->json($contorno);
    }

    public function update(Request $request, $id)
    {
        $contorno = MateriaPrimaContorno::find($id);

        if (!$contorno) {
            return response()->json(['message' => 'Contorno no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => 'string|nullable',
            'descripcion' => 'sometimes|string',
            'factor_desperdicio' => 'sometimes|numeric|min:0|max:100',
            'categoria_id' => 'sometimes|exists:categorias,id',
            'id_lugar' => 'sometimes|exists:lugars,id',
            'sub_categoria_id' => 'sometimes|exists:sub_categorias,id',
            'stock_global_actual' => 'sometimes|integer|min:0',
            'stock_global_minimo' => 'sometimes|integer|min:0',
            'id_sucursal' => 'sometimes|exists:sucursal,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('imagen')) {
            if ($contorno->imagen && file_exists(public_path($contorno->imagen))) {
                unlink(public_path($contorno->imagen));
            }

            $file = $request->file('imagen');
            $filename = $contorno->id . '.' . $file->getClientOriginalExtension();
            $path = public_path('storage/materias_primas_contorno');

            if (!file_exists($path)) mkdir($path, 0777, true);

            $file->move($path, $filename);
            $contorno->imagen = 'storage/materias_primas_contorno/' . $filename;
        }

        $contorno->update($request->except('imagen'));
        $contorno->save();

        return response()->json($contorno);
    }

    public function destroy($id)
    {
        $contorno = MateriaPrimaContorno::find($id);

        if (!$contorno) {
            return response()->json(['message' => 'Contorno no encontrado'], 404);
        }

        if ($contorno->imagen && file_exists(public_path($contorno->imagen))) {
            unlink(public_path($contorno->imagen));
        }

        $contorno->delete();

        return response()->json(['message' => 'Contorno eliminado correctamente']);
    }

    public function indexPaginado(Request $request)
    {
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $totalItems = MateriaPrimaContorno::count();
        $totalPages = ceil($totalItems / $perPage);

        $contornos = MateriaPrimaContorno::latest()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'data' => $contornos
        ]);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search', '');

        if (empty($searchTerm)) {
            return response()->json(MateriaPrimaContorno::all());
        }

        $contornos = MateriaPrimaContorno::where('descripcion', 'LIKE', "%{$searchTerm}%")->get();

        if ($contornos->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron coincidencias para: ' . $searchTerm,
                'data' => []
            ]);
        }

        return response()->json($contornos);
    }

    public function searchCategorias(Request $request)
    {
        $categoria = $request->input('categoria_id');
        $subCategoria = $request->input('sub_categoria_id');
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $query = MateriaPrimaContorno::query();

        if ($categoria) {
            $query->where('categoria_id', $categoria);
        }

        if ($subCategoria) {
            $query->where('sub_categoria_id', $subCategoria);
        }

        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        $contornos = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'filters' => [
                'categoria_id' => $categoria,
                'sub_categoria_id' => $subCategoria
            ],
            'data' => $contornos
        ]);
    }
}
