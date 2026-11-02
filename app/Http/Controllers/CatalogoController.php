<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\SubCategoria;
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

        $categoria = null;
        $subCategoria = null;

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
