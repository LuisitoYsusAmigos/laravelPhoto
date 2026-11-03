<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\SubCategoria;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;

class CatalogoController extends Controller
{
    /**
     * Obtiene la consulta filtrada de productos para el catálogo.
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
            'sucursal' => $sucursal,
        ];
    }

    /**
     * Endpoint GET para visualizar el catálogo en formato HTML.
     */
    public function htmlCatalogo(Request $request)
    {
        $datos = $this->obtenerDatosCatalogo($request);
        $datos['esPdf'] = false;

        return view('pdf.catalogo', $datos);
    }

    /**
     * Endpoint GET para descargar/visualizar el catálogo en formato PDF.
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
}
