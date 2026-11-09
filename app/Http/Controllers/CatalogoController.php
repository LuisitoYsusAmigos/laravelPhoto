<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\SubCategoria;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;

class CatalogoController extends Controller
{
    /**
     * Normaliza un color en formato hexadecimal.
     */
    private function normalizarColorHex($color, $default)
    {
        if (empty($color) || !is_string($color)) {
            return $default;
        }

        $color = trim($color);
        if (!str_starts_with($color, '#')) {
            $color = '#' . $color;
        }

        return preg_match('/^#[a-fA-F0-9]{3,8}$/', $color) ? $color : $default;
    }

    /**
     * Obtiene la consulta filtrada de productos para el catálogo v1 (GET).
     */
    private function obtenerDatosCatalogo(Request $request)
    {
        $query = Producto::with(['categoria', 'subCategoria'])
            ->where('visibilidad', 1);

        $categoriaId = $request->input('categoria_id') ?? $request->input('categoria');
        $subCategoriaId = $request->input('sub_categoria_id') ?? $request->input('sub_categoria') ?? $request->input('subcategoria');
        $sucursalId = $request->input('id_sucursal') ?? $request->input('sucursal_id') ?? $request->input('sucursal');

        $categoria = null;
        $subCategoria = null;
        $sucursal = null;

        // Si se envía sucursal
        if (!empty($sucursalId)) {
            $sucursal = Sucursal::find($sucursalId);
            $query->where('id_sucursal', $sucursalId);
        }

        if (!$sucursal) {
            $sucursal = Sucursal::first();
        }

        // Si se envía categoría
        if (!empty($categoriaId)) {
            $query->where('categoria_id', $categoriaId);
            $categoria = Categoria::find($categoriaId);
        }

        // Si se envía subcategoría
        if (!empty($subCategoriaId)) {
            $query->where('sub_categoria_id', $subCategoriaId);
            $subCategoria = SubCategoria::find($subCategoriaId);
        }

        $productos = $query->orderBy('id', 'asc')->get();

        return [
            'productos' => $productos,
            'categoria' => $categoria,
            'subCategoria' => $subCategoria,
            'categoriaTexto' => $categoria ? $categoria->nombre : null,
            'subCategoriaTexto' => $subCategoria ? $subCategoria->nombre : null,
            'sucursal' => $sucursal,
            'colorPrimario' => '#c7191d',
            'colorSecundario' => '#050505',
            'sizeImage' => 1.0,
            'mostrarNombreCategorias' => true,
        ];
    }

    /**
     * Obtiene y valida la consulta filtrada de productos para el catálogo v2 (POST).
     */
    private function obtenerDatosCatalogoV2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'catalogo' => 'required|array',
            'catalogo.sucursal_id' => 'required',
            'catalogo.categoria_ids' => 'required',
            'catalogo.subcategoria_ids' => 'required',
            'formato_catalogo' => 'nullable|array',
            'formato_catalogo.color_primario' => 'nullable|string',
            'formato_catalogo.color_secundario' => 'nullable|string',
            'formato_catalogo.sizeImage' => 'nullable|numeric|min:0.1',
            'formato_catalogo.mostrar_nombre_categorias' => 'nullable|boolean',
        ], [
            'catalogo.required' => 'El objeto catalogo es obligatorio.',
            'catalogo.sucursal_id.required' => 'El id de la sucursal (sucursal_id) es obligatorio.',
            'catalogo.categoria_ids.required' => 'El campo categoria_ids es obligatorio (arreglo de IDs o "*").',
            'catalogo.subcategoria_ids.required' => 'El campo subcategoria_ids es obligatorio (arreglo de IDs o "*").',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validación fallida en los datos enviados',
                'errors' => $validator->errors()
            ], 422);
        }

        $catalogo = $request->input('catalogo', []);
        $formato = $request->input('formato_catalogo', []);

        $query = Producto::with(['categoria', 'subCategoria'])
            ->where('visibilidad', 1);

        // Sucursal
        $sucursalId = $catalogo['sucursal_id'] ?? $catalogo['id_sucursal'] ?? null;
        $sucursal = Sucursal::find($sucursalId);
        if (!$sucursal) {
            $sucursal = Sucursal::first();
        }
        if ($sucursal) {
            $query->where('id_sucursal', $sucursal->id);
        }

        // Categorías
        $rawCategorias = $catalogo['categoria_ids'] ?? null;
        $todasCategorias = ($rawCategorias === '*' || (is_array($rawCategorias) && in_array('*', $rawCategorias)));
        $categorias = collect();
        $categoriaTexto = null;

        if (!$todasCategorias) {
            $categoriaIds = is_array($rawCategorias) ? $rawCategorias : [$rawCategorias];
            $query->whereIn('categoria_id', $categoriaIds);
            $categorias = Categoria::whereIn('id', $categoriaIds)->get();
            $categoriaTexto = $categorias->pluck('nombre')->implode(', ');
        } else {
            $categoriaTexto = 'Todas las categorías';
        }

        // Subcategorías
        $rawSubcategorias = $catalogo['subcategoria_ids'] ?? null;
        $todasSubcategorias = ($rawSubcategorias === '*' || (is_array($rawSubcategorias) && in_array('*', $rawSubcategorias)));
        $subCategorias = collect();
        $subCategoriaTexto = null;

        if (!$todasSubcategorias) {
            $subcategoriaIds = is_array($rawSubcategorias) ? $rawSubcategorias : [$rawSubcategorias];
            $query->whereIn('sub_categoria_id', $subcategoriaIds);
            $subCategorias = SubCategoria::whereIn('id', $subcategoriaIds)->get();
            $subCategoriaTexto = $subCategorias->pluck('nombre')->implode(', ');
        } else {
            $subCategoriaTexto = 'Todas las subcategorías';
        }

        // Formato de catálogo
        $colorPrimario = $this->normalizarColorHex($formato['color_primario'] ?? null, '#c7191d');
        $colorSecundario = $this->normalizarColorHex($formato['color_secundario'] ?? null, '#050505');
        $sizeImage = isset($formato['sizeImage']) ? (float)$formato['sizeImage'] : 1.0;
        $mostrarNombreCategorias = filter_var($formato['mostrar_nombre_categorias'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $productos = $query->orderBy('id', 'asc')->get();

        return [
            'productos' => $productos,
            'categoria' => null,
            'subCategoria' => null,
            'categorias' => $categorias,
            'subCategorias' => $subCategorias,
            'categoriaTexto' => $categoriaTexto,
            'subCategoriaTexto' => $subCategoriaTexto,
            'sucursal' => $sucursal,
            'colorPrimario' => $colorPrimario,
            'colorSecundario' => $colorSecundario,
            'sizeImage' => $sizeImage,
            'mostrarNombreCategorias' => $mostrarNombreCategorias,
        ];
    }

    /**
     * Endpoint GET para visualizar el catálogo en formato HTML (v1).
     */
    public function htmlCatalogo(Request $request)
    {
        $datos = $this->obtenerDatosCatalogo($request);
        $datos['esPdf'] = false;

        return view('pdf.catalogo', $datos);
    }

    /**
     * Endpoint GET para descargar/visualizar el catálogo en formato PDF (v1).
     */
    public function getPdfCatalogo(Request $request)
    {
        $datos = $this->obtenerDatosCatalogo($request);
        $datos['esPdf'] = true;

        $pdf = Pdf::loadView('pdf.catalogo', $datos)
            ->setPaper('a4', 'portrait');

        $nombreArchivo = 'catalogo_productos';
        if ($datos['categoria']) {
            $nombreArchivo .= '_' . str_replace(' ', '_', strtolower($datos['categoria']->nombre));
        }
        if ($datos['subCategoria']) {
            $nombreArchivo .= '_' . str_replace(' ', '_', strtolower($datos['subCategoria']->nombre));
        }

        return $pdf->stream($nombreArchivo . '.pdf');
    }

    /**
     * Endpoint POST para visualizar el catálogo en formato HTML (v2).
     */
    public function htmlCatalogoV2(Request $request)
    {
        $datos = $this->obtenerDatosCatalogoV2($request);
        if ($datos instanceof \Illuminate\Http\JsonResponse) {
            return $datos;
        }

        $datos['esPdf'] = false;

        return view('pdf.catalogo', $datos);
    }

    /**
     * Endpoint POST para descargar/visualizar el catálogo en formato PDF (v2).
     */
    public function getPdfCatalogoV2(Request $request)
    {
        $datos = $this->obtenerDatosCatalogoV2($request);
        if ($datos instanceof \Illuminate\Http\JsonResponse) {
            return $datos;
        }

        $datos['esPdf'] = true;

        $pdf = Pdf::loadView('pdf.catalogo', $datos)
            ->setPaper('a4', 'portrait');

        $nombreArchivo = 'catalogo_productos_v2';
        return $pdf->stream($nombreArchivo . '.pdf');
    }
}
