<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CalculosSistema;

use App\Models\MateriaPrimaVarilla;
use App\Models\MateriaPrimaVidrio;
use App\Models\MateriaPrimaTrupan;
use App\Models\Producto;
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
            'tipo' => 'varilla',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock_global_actual,
            'sucursal_id' => $item->id_sucursal,
            'imagen'=> $item->imagen,
        ];
    });

    $vidrios = MateriaPrimaVidrio::where('descripcion', 'LIKE', "%$search%")->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'tipo' => 'vidrio',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock_global_actual,
            'sucursal_id' => $item->id_sucursal,
            'imagen'=> $item->imagen,
        ];
    });

    $trupan = MateriaPrimaTrupan::where('descripcion', 'LIKE', "%$search%")->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'tipo' => 'trupan',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock_global_actual,
            'sucursal_id' => $item->id_sucursal,
            'imagen'=> $item->imagen,
        ];
    });

    $productos = Producto::where('descripcion', 'LIKE', "%$search%")->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'tipo' => 'producto',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria_id,
            'stock' => $item->stock,
            'sucursal_id' => $item->sucursal_id,
            'imagen'=> $item->imagen,
        ];
    });

    $resultados = $varillas->concat($vidrios)->concat($trupan)->concat($productos)->values();

    // Paginación manual
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
            'tipo' => 'varilla',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock_global_actual,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
        ];
    });

    $vidrios = MateriaPrimaVidrio::all()->map(function ($item) {
        return [
            'id' => $item->id,
            'tipo' => 'vidrio',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock_global_actual,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
        ];
    });

    $trupan = MateriaPrimaTrupan::all()->map(function ($item) {
        return [
            'id' => $item->id,
            'tipo' => 'trupan',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock_global_actual,
            'sucursal_id' => $item->id_sucursal,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
        ];
    });

    $productos = Producto::all()->map(function ($item) {
        return [
            'id' => $item->id,
            'tipo' => 'producto',
            'descripcion' => $item->descripcion,
            'categoria' => $item->categoria,
            'sub_categoria' => $item->sub_categoria,
            'stock' => $item->stock,
            'sucursal_id' => $item->sucursal_id,
            'created_at' => $item->created_at,
            'imagen'=> $item->imagen,
        ];
    });

    $todos = $varillas->concat($vidrios)
                      ->concat($trupan)
                      ->concat($productos)
                      ->sortByDesc('created_at') // 👈 Ordenar por fecha descendente
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


