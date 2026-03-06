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
        $stock = [
            'largo' => $request->largo,
            'alto' => $request->alto,
            'precio' => $request->precioCompra,
            'stock' => $request->stock_global_actual, // 'stock' en lugar de 'cantidad'
            'contable' => true, // Si es necesario
            'id_materia_prima_contorno' => $contorno->id, // Relación con el contorno
        ];
        $stockController = new StockContornoController();
        $stockController->store(new Request($stock));

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
            //'stock_global_minimo' => 'sometimes|integer|min:0',
            'id_sucursal' => 'sometimes|exists:sucursal,id',
            'visibilidad' => 'nullable|boolean',
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
        $stock_global_Nuevo = $request->input('stock_global_actual');
        $totalStock = \App\Models\StockContorno::where('id_materia_prima_contorno', $id)->sum('stock');
        if($totalStock>=$stock_global_Nuevo){
            $contorno->stock_global_actual = $stock_global_Nuevo;
            $contorno->save();
            //dd('se puede');
            // si se puede quiero que vayas descontando de los stock con este ide prodcuto desde el stock con id mas bajo al mas alto, si el stock llega a 0 lo actualizas y vas al siguiente stock asi vaciando uno por uno hasta que se complete el stock_global_Nuevo
            
            $stocks = \App\Models\StockContorno::where('id_materia_prima_contorno', $id)->orderBy('id', 'asc')->get();
            $cantidad_a_descontar = $totalStock - $stock_global_Nuevo;

            foreach ($stocks as $s) {
                if ($cantidad_a_descontar <= 0) {
                    break;
                }
                
                if ($s->stock >= $cantidad_a_descontar) {
                    $s->stock -= $cantidad_a_descontar;
                    $s->save();
                    $cantidad_a_descontar = 0; // Ya se descontó todo
                } else {
                    $cantidad_a_descontar -= $s->stock;
                    $s->stock = 0; // Se vació este lote de stock
                    $s->save();
                }
            }
        }else{
            return response()->json(['message' => 'No se puede actualizar el stock, el stock ingresado es mayor al stock actual'], 400);// cambia el mensaje de erorr, se gardaron todoso los camposon excepto por stock debido a que no se pudo actualizar el stock    
        }

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
        // Obtener parámetros de paginación con valores por defecto
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('perPage', 10);
        $visibilidad = $request->input('visibilidad');
        // Inicializar la consulta de productos
        $query = MateriaPrimaContorno::query();
        
        if($visibilidad === null){
            $query->where('visibilidad', 1);
        }elseif($visibilidad == 0){
            $query->where('visibilidad', 0);
        }
        // Obtener el total de registros con el filtro aplicado
        $totalItems = $query->count();

        // Calcular el total de páginas
        $totalPages = $perPage > 0 ? ceil($totalItems / $perPage) : 1;

        // Obtener los productos paginados preservando el filtro
        $contornos = $query->latest()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Devolver la respuesta con el formato solicitado
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
