<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MateriaPrimaVarilla;
use Illuminate\Support\Facades\Validator;

class MateriaPrimaVarillaController extends Controller
{
    public function index()
    {
        return response()->json(MateriaPrimaVarilla::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string',
            'precioCompra' => 'required|numeric|min:0',
            'precioVenta' => 'required|numeric|min:0',
            'largo' => 'required|integer',
            'grosor' => 'required|integer',
            'ancho' => 'required|integer',
            'factor_desperdicio' => 'required|numeric|between:0,100',
            'categoria_id' => 'required|exists:categorias,id',
            'sub_categoria_id' => 'required|exists:sub_categorias,id',
            'stock_global_actual' => 'required|integer',
            'stock_global_minimo' => 'required|integer',
            'id_sucursal' => 'required|exists:sucursal,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data = $request->except('imagen', 'stock_global_actual');
        $data['stock_global_actual'] = 0;

        $varilla = MateriaPrimaVarilla::create($request->except('imagen'));

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = $varilla->id . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('storage/materias_primas');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $filename);
            $varilla->imagen = 'storage/materias_primas/' . $filename;
            $varilla->save();
        }

        
        
        $stock = [
            'largo' => $request->largo,
            'precio' => $request->precioCompra,
            'stock' => $request->stock_global_actual,
            'contable' => true,
            'id_materia_prima_varilla' => $varilla->id,
        ];
        // LLAMO AL CONTROLADOR DE STOCK varilla
        $stockController = new StockVarillaController();
        $stockController->store(new Request($stock));

        return response()->json($varilla, 201);
    }

    public function show($id)
    {
        $varilla = MateriaPrimaVarilla::find($id);

        if (!$varilla) {
            return response()->json(['message' => 'Varilla no encontrada'], 404);
        }

        return response()->json($varilla);
    }

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
            'categoria_id' => 'sometimes|exists:categorias,id',
            'sub_categoria_id' => 'sometimes|exists:sub_categorias,id',
            'stock_global_actual' => 'sometimes|integer',
            'stock_global_minimo' => 'sometimes|integer',
            'id_sucursal' => 'sometimes|exists:sucursal,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            //'imagen' => 'image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('imagen')) {
            if ($varilla->imagen && file_exists(public_path($varilla->imagen))) {
                unlink(public_path($varilla->imagen));
            }

            $file = $request->file('imagen');
            $filename = $varilla->id . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('storage/materias_primas');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $filename);
            $varilla->imagen = 'storage/materias_primas/' . $filename;
        }

        $varilla->update($request->except('imagen'));
        $varilla->save();

        return response()->json($varilla);
    }

    public function destroy($id)
    {
        $varilla = MateriaPrimaVarilla::find($id);

        if (!$varilla) {
            return response()->json(['message' => 'Varilla no encontrada'], 404);
        }

        if ($varilla->imagen && file_exists(public_path($varilla->imagen))) {
            unlink(public_path($varilla->imagen));
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

        $varillas = MateriaPrimaVarilla::latest()
            ->skip(($page - 1) * $perPage)
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

        $varillas = MateriaPrimaVarilla::where('descripcion', 'LIKE', "%{$searchTerm}%")->get();

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
        $categoria = $request->input('categoria_id');
        $subCategoria = $request->input('sub_categoria_id');
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $query = MateriaPrimaVarilla::query();

        if ($categoria) {
            $query->where('categoria_id', $categoria);
        }

        if ($subCategoria) {
            $query->where('sub_categoria_id', $subCategoria);
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
                'categoria_id' => $categoria,
                'sub_categoria_id' => $subCategoria
            ],
            'data' => $varillas
        ]);
    }
}
