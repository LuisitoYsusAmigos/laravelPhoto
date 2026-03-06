<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MateriaPrimaTrupan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MateriaPrimaTrupanController extends Controller
{
    public function index()
    {
        return response()->json(MateriaPrimaTrupan::all());
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
            'grosor' => 'required|integer|min:1',
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

        $trupan = MateriaPrimaTrupan::create($request->except('imagen'));

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = $trupan->id . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('storage/materias_primas_trupan');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $filename);
            $trupan->imagen = 'storage/materias_primas_trupan/' . $filename;
            $trupan->save();
        }
        //DB::statement('SET @DISABLE_TRIGGERS = 1');
        $stock =[
            'alto' => $request->alto,
            'largo' => $request->largo,
            'precio' => $request->precioCompra,
            'stock' => $request->stock_global_actual,
            'contable' => true,
            'id_materia_prima_trupans' => $trupan->id,
        ];
        //llamar al controlador de stock
        $stockTrupanController = new StockTrupanController();
        $stockTrupanController->store(new Request($stock));
        //DB::statement('SET @DISABLE_TRIGGERS = 0');
        //llamar al controlador de stock
        

        

        return response()->json($trupan, 201);
    }

    public function show($id)
    {
        $trupan = MateriaPrimaTrupan::find($id);

        if (!$trupan) {
            return response()->json(['message' => 'Trupan no encontrado'], 404);
        }

        return response()->json($trupan);
    }

    public function update(Request $request, $id)
    {
        $trupan = MateriaPrimaTrupan::find($id);

        if (!$trupan) {
            return response()->json(['message' => 'Trupan no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => 'string|nullable',
            'descripcion' => 'sometimes|string',
            'grosor' => 'sometimes|integer|min:1',
            'factor_desperdicio' => 'sometimes|numeric|min:0|max:100',
            'categoria_id' => 'sometimes|exists:categorias,id',
            'id_lugar' => 'sometimes|exists:lugars,id',
            'sub_categoria_id' => 'sometimes|exists:sub_categorias,id',
            //'stock_global_actual' => 'sometimes|integer|min:0',
            'stock_global_minimo' => 'sometimes|integer|min:0',
            'id_sucursal' => 'sometimes|exists:sucursal,id',
            'visibilidad' => 'nullable|boolean',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('imagen')) {
            if ($trupan->imagen && file_exists(public_path($trupan->imagen))) {
                unlink(public_path($trupan->imagen));
            }

            $file = $request->file('imagen');
            $filename = $trupan->id . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('storage/materias_primas_trupan');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $filename);
            $trupan->imagen = 'storage/materias_primas_trupan/' . $filename;
        }

        $trupan->update($request->except('imagen'));
        $trupan->save();



        $stock_global_Nuevo = $request->input('stock_global_actual');
        $totalStock = \App\Models\StockTrupan::where('id_materia_prima_trupans', $id)->sum('stock');
        if($totalStock>=$stock_global_Nuevo){
            $trupan->stock_global_actual = $stock_global_Nuevo;
            $trupan->save();
            //dd('se puede');
            // si se puede quiero que vayas descontando de los stock con este ide prodcuto desde el stock con id mas bajo al mas alto, si el stock llega a 0 lo actualizas y vas al siguiente stock asi vaciando uno por uno hasta que se complete el stock_global_Nuevo
            
            $stocks = \App\Models\StockTrupan::where('id_materia_prima_trupans', $id)->orderBy('id', 'asc')->get();
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

        return response()->json($trupan);
    }

    public function destroy($id)
    {
        $trupan = MateriaPrimaTrupan::find($id);

        if (!$trupan) {
            return response()->json(['message' => 'Trupan no encontrado'], 404);
        }

        if ($trupan->imagen && file_exists(public_path($trupan->imagen))) {
            unlink(public_path($trupan->imagen));
        }

        $trupan->delete();

        return response()->json(['message' => 'Trupan eliminado correctamente']);
    }

    public function indexPaginado(Request $request)
    {
        // Obtener parámetros de paginación con valores por defecto
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('perPage', 10);
        $visibilidad = $request->input('visibilidad');
        // Inicializar la consulta de productos
        $query = MateriaPrimaTrupan::query();
        
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
        $trupans = $query->latest()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Devolver la respuesta con el formato solicitado
        return response()->json([
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'data' => $trupans
        ]);
    }


    public function search(Request $request)
    {
        $searchTerm = $request->input('search', '');

        if (empty($searchTerm)) {
            return response()->json(MateriaPrimaTrupan::all());
        }

        $trupanes = MateriaPrimaTrupan::where('descripcion', 'LIKE', "%{$searchTerm}%")->get();

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
        $categoria = $request->input('categoria_id');
        $subCategoria = $request->input('sub_categoria_id');
        $page = max((int)$request->input('page', 1), 1);
        $perPage = max((int)$request->input('perPage', 10), 1);

        $query = MateriaPrimaTrupan::query();

        if ($categoria) {
            $query->where('categoria_id', $categoria);
        }

        if ($subCategoria) {
            $query->where('sub_categoria_id', $subCategoria);
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
                'categoria_id' => $categoria,
                'sub_categoria_id' => $subCategoria
            ],
            'data' => $trupanes
        ]);
    }
}
