<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CalculosSistema;

use App\Models\MateriaPrimaVarilla;
use App\Models\MateriaPrimaVidrio;
use App\Models\MateriaPrimaTrupan;
use App\Models\Producto;
use App\Models\MateriaPrimaContorno;

class CotizadorController extends Controller
{
    public function calcular(Request $request, CalculosSistema $calculos)
    {
        $ancho = $request->query('ancho');
        $alto = $request->query('alto');
        $grosor = $request->query('grosor');

        // Validación manual: que todos sean números enteros positivos
        $errores = [];

        if (!ctype_digit($ancho)) {
            $errores['ancho'] = 'El ancho debe ser un número entero positivo.';
        }

        if (!ctype_digit($alto)) {
            $errores['alto'] = 'El alto debe ser un número entero positivo.';
        }

        if (!ctype_digit($grosor)) {
            $errores['grosor'] = 'El grosor debe ser un número entero positivo.';
        }

        if (!empty($errores)) {
            return response()->json([
                'success' => false,
                'errores' => $errores,
            ], 422);
        }

        // Convertimos los strings validados a int
        $ancho = (int) $ancho;
        $alto = (int) $alto;
        $grosor = (int) $grosor;

        $resultado = $calculos->calcularMargenExterno($ancho, $alto, $grosor);

        return response()->json([
            'success' => true,
            'datos' => $resultado,
        ]);
    }

    public function indexPaginadoGeneral(Request $request)
{
    $page = (int) $request->input('page', 1);
    $perPage = (int) $request->input('perPage', 10);

    // Obtener y mapear cada modelo
    $varillas = MateriaPrimaVarilla::all()->map(function ($item) {
        return [
            'tipo' => 'varilla',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock_global_actual,
            'sucursal_id' => $item->id_sucursal,
        ];
    });

    $vidrios = MateriaPrimaVidrio::all()->map(function ($item) {
        return [
            'tipo' => 'vidrio',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock_global_actual,
            'sucursal_id' => $item->id_sucursal,
        ];
    });

    $trupan = MateriaPrimaTrupan::all()->map(function ($item) {
        return [
            'tipo' => 'trupan',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock_global_actual,
            'sucursal_id' => $item->id_sucursal,
        ];
    });

    $productos = Producto::all()->map(function ($item) {
        return [
            'tipo' => 'producto',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'stock' => $item->stock,
            'sucursal_id' => $item->sucursal_id,
        ];
    });

    // Unificar resultados
    $todos = $varillas->concat($vidrios)
                      ->concat($trupan)
                      ->concat($productos)
                      ->values();

    // Paginación manual
    $total = $todos->count();
    $totalPages = ceil($total / $perPage);
    $resultados = $todos->slice(($page - 1) * $perPage, $perPage)->values();

    return response()->json([
        'currentPage' => $page,
        'perPage' => $perPage,
        'totalItems' => $total,
        'totalPages' => $totalPages,
        'data' => $resultados
    ]);
}


public function searchPaginadoGeneral(Request $request)
{
    $search = $request->input('search', '');
    $page = (int) $request->input('page', 1);
    $perPage = (int) $request->input('perPage', 10);

    // Buscar en cada tabla
    $varillas = MateriaPrimaVarilla::where('descripcion', 'LIKE', "%$search%")->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'varilla',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
            'grosor'=> $item->grosor,
            'ancho'=> $item->ancho,
            'stock_global_actual'=> $item->stock_global_actual,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'factor_desperdicio'=> $item->factor_desperdicio,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'largo'=> $item->largo,
            'id_lugar' => $item->id_lugar,
        ];
    });

    $vidrios = MateriaPrimaVidrio::where('descripcion', 'LIKE', "%$search%")->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'vidrio',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
            'grosor'=> $item->grosor,
            'alto'=> $item->alto,
            'largo'=> $item->largo,
            'stock_global_actual'=> $item->stock_global_actual,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'factor_desperdicio'=> $item->factor_desperdicio,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'id_lugar' => $item->id_lugar,
        ];
    });

    $trupan = MateriaPrimaTrupan::where('descripcion', 'LIKE', "%$search%")->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'trupan',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
            'grosor'=> $item->grosor,
            'alto'=> $item->alto,
            'largo'=> $item->largo,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'stock_global_actual'=> $item->stock_global_actual,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'factor_desperdicio'=> $item->factor_desperdicio,
            'id_lugar' => $item->id_lugar,

        ];
    });

    $contornos = MateriaPrimaContorno::where('descripcion', 'LIKE', "%$search%")->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'contorno',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
            'alto'=> $item->alto,
            'largo'=> $item->largo,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'stock_global_actual'=> $item->stock_global_actual,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'factor_desperdicio'=> $item->factor_desperdicio,
            'id_lugar' => $item->id_lugar,
        ];
    });

    $productos = Producto::where('descripcion', 'LIKE', "%$search%")->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'producto',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'stock_global_actual' => $item->stock_global_actual,
            'sucursal_id' => $item->sucursal_id,
            'imagen'=> $item->imagen,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'id_lugar' => $item->id_lugar,
        ];
    });

    $resultados = $varillas
        ->concat($vidrios)
        ->concat($trupan)
        ->concat($contornos)
        ->concat($productos)
        ->values();

    $total = $resultados->count();
    $totalPages = ceil($total / $perPage);
    $pagina = $resultados->slice(($page - 1) * $perPage, $perPage)->values();

    return response()->json([
        'currentPage' => $page,
        'perPage' => $perPage,
        'totalItems' => $total,
        'totalPages' => $totalPages,
        'data' => $pagina
    ]);
}



public function indexPaginadoGeneralPorMasReciente(Request $request)
{
    $page = (int) $request->input('page', 1);
    $perPage = (int) $request->input('perPage', 10);

    $varillas = MateriaPrimaVarilla::all()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'varilla',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
            'grosor'=> $item->grosor,
            'ancho'=> $item->ancho,
            'stock_global_actual'=> $item->stock_global_actual,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'factor_desperdicio'=> $item->factor_desperdicio,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'largo'=> $item->largo,
            'id_lugar' => $item->id_lugar,
        ];
    });

    $vidrios = MateriaPrimaVidrio::all()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'vidrio',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
            'grosor'=> $item->grosor,
            'alto'=> $item->alto,
            'largo'=> $item->largo,
            'stock_global_actual'=> $item->stock_global_actual,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'factor_desperdicio'=> $item->factor_desperdicio,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'id_lugar' => $item->id_lugar,
        ];
    });

    $trupan = MateriaPrimaTrupan::all()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'trupan',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
            'grosor'=> $item->grosor,
            'alto'=> $item->alto,
            'largo'=> $item->largo,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'stock_global_actual'=> $item->stock_global_actual,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'factor_desperdicio'=> $item->factor_desperdicio,
            'id_lugar' => $item->id_lugar,

        ];
    });

    $contornos = MateriaPrimaContorno::all()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'contorno',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
            'alto'=> $item->alto,
            'largo'=> $item->largo,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'stock_global_actual'=> $item->stock_global_actual,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'factor_desperdicio'=> $item->factor_desperdicio,
            'id_lugar' => $item->id_lugar,
        ];
    });

    $productos = Producto::all()->map(function ($item) {
        return [
            'id' => $item->id,
            'codigo' => $item->codigo,
            'tipo' => 'producto',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria_id,
            'sub_categoria' => $item->sub_categoria_id,
            'stock_global_actual' => $item->stock_global_actual,
            'sucursal_id' => $item->sucursal_id,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
            'precioCompra'=> $item->precioCompra,
            'precioVenta'=> $item->precioVenta,
            'stock_global_minimo'=> $item->stock_global_minimo,
            'id_lugar' => $item->id_lugar,
        ];
    });

    $todos = $varillas
        ->concat($vidrios)
        ->concat($trupan)
        ->concat($contornos)
        ->concat($productos)
        ->sortByDesc('created_at')
        ->values();

    $total = $todos->count();
    $totalPages = ceil($total / $perPage);
    $resultados = $todos->slice(($page - 1) * $perPage, $perPage)->values();

    return response()->json([
        'currentPage' => $page,
        'perPage' => $perPage,
        'totalItems' => $total,
        'totalPages' => $totalPages,
        'data' => $resultados
    ]);
}




    
}


