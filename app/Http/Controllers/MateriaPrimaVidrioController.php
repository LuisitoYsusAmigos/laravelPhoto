<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MateriaPrimaVidrio;
use Illuminate\Support\Facades\Validator;

class MateriaPrimaVidrioController extends Controller
{
    public function index()
    {
        return response()->json(MateriaPrimaVidrio::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string',
            'precioCompra' => 'required|numeric|min:0',
            'precioVenta' => 'required|numeric|min:0',
            'alto' => 'required|integer|min:1',
            'largo' => 'required|integer|min:1',
            'grosor' => 'required|integer|min:1',
            'factor_desperdicio' => 'required|numeric|min:0|max:100',
            'categoria_id' => 'required|exists:categorias,id',
            'sub_categoria_id' => 'required|exists:sub_categorias,id',
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

        $vidrio = MateriaPrimaVidrio::create($request->except('imagen'));

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = $vidrio->id . '.' . $file->getClientOriginalExtension();
            $path = public_path('storage/materias_primas_vidrio');

            if (!file_exists($path)) mkdir($path, 0777, true);

            $file->move($path, $filename);
            $vidrio->imagen = 'storage/materias_primas_vidrio/' . $filename;
            
            $vidrio->save();
        }

        $stock = [
            'largo' => $request->largo,
            'alto' => $request->alto,
            'precio' => $request->precioCompra,
            'stock' => $request->stock_global_actual,
            'contable' => true,
            'id_materia_prima_vidrio' => $vidrio->id,
        ];
        // llamar al controlador de stock
        $stockController = new StockVidrioController();// StockVidrioController::store($stock);
        $stockController->store(new Request($stock));
        
        return response()->json($vidrio, 201);
    }

    public function show($id)
    {
        $vidrio = MateriaPrimaVidrio::find($id);

        if (!$vidrio) {
            return response()->json(['message' => 'Vidrio no encontrado'], 404);
        }

        return response()->json($vidrio);
    }

    public function update(Request $request, $id)
    {
        $vidrio = MateriaPrimaVidrio::find($id);

        if (!$vidrio) {
            return response()->json(['message' => 'Vidrio no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'descripcion' => 'sometimes|string',
            'grosor' => 'sometimes|integer|min:1',
            'factor_desperdicio' => 'sometimes|numeric|min:0|max:100',
            'categoria_id' => 'sometimes|exists:categorias,id',
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
            if ($vidrio->imagen && file_exists(public_path($vidrio->imagen))) {
                unlink(public_path($vidrio->imagen));
            }

            $file = $request->file('imagen');
            $filename = $vidrio->id . '.' . $file->getClientOriginalExtension();
            $path = public_path('storage/materias_primas_vidrio');

            if (!file_exists($path)) mkdir($path, 0777, true);

            $file->move($path, $filename);
            $vidrio->imagen = 'storage/materias_primas_vidrio/' . $filename;
        }

        $vidrio->update($request->except('imagen'));
        $vidrio->save();

        return response()->json($vidrio);
    }

    public function destroy($id)
    {
        $vidrio = MateriaPrimaVidrio::find($id);

        if (!$vidrio) {
            return response()->json(['message' => 'Vidrio no encontrado'], 404);
        }

        if ($vidrio->imagen && file_exists(public_path($vidrio->imagen))) {
            unlink(public_path($vidrio->imagen));
        }

        $vidrio->delete();

        return response()->json(['message' => 'Vidrio eliminado correctamente']);
    }

    public function indexPaginado(Request $request)
    {
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $totalItems = MateriaPrimaVidrio::count();
        $totalPages = ceil($totalItems / $perPage);

        $vidrios = MateriaPrimaVidrio::latest()
        ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'data' => $vidrios
        ]);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search', '');

        if (empty($searchTerm)) {
            return response()->json(MateriaPrimaVidrio::all());
        }

        $vidrios = MateriaPrimaVidrio::where('descripcion', 'LIKE', "%{$searchTerm}%")->get();

        if ($vidrios->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron coincidencias para: ' . $searchTerm,
                'data' => []
            ]);
        }

        return response()->json($vidrios);
    }

    public function searchCategorias(Request $request)
    {
        $categoria = $request->input('categoria_id');
        $subCategoria = $request->input('sub_categoria_id');
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $query = MateriaPrimaVidrio::query();

        if ($categoria) {
            $query->where('categoria_id', $categoria);
        }

        if ($subCategoria) {
            $query->where('sub_categoria_id', $subCategoria);
        }

        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $perPage);

        $vidrios = $query->skip(($page - 1) * $perPage)
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
            'data' => $vidrios
        ]);
    }
}
