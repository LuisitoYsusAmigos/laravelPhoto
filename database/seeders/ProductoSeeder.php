<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\SubCategoria;
use App\Models\Lugar;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\ProductoController;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear una categoría genérica
        $categoria = Categoria::firstOrCreate(
            ['nombre' => 'General'],
            ['tipo' => 'Producto Estandar']
        );

        // Crear una sub-categoría genérica
        $subCategoria = SubCategoria::firstOrCreate(
            ['nombre' => 'Subcategoría General', 'id_categoria' => $categoria->id]
        );

        // Crear un lugar genérico
        $lugar = Lugar::firstOrCreate(
            ['nombre' => 'Estante 1']
        );

        // Obtener la sucursal (creada previamente en DatabaseSeeder)
        $sucursal = Sucursal::first();
        $idSucursal = $sucursal ? $sucursal->id : 1;

        $productos = [
            [
                'codigo' => 'PROD-001',
                'descripcion' => 'Producto de prueba 1',
                'precioCompra' => 50,
                'precioVenta' => 100,
                'stock_global_actual' => 20,
                'stock_global_minimo' => 5,
                'actualizacion' => Carbon::now()->toDateString(),
                'id_sucursal' => $idSucursal,
                'categoria_id' => $categoria->id,
                'sub_categoria_id' => $subCategoria->id,
                'id_lugar' => $lugar->id,
            ],
            [
                'codigo' => 'PROD-002',
                'descripcion' => 'Producto de prueba 2',
                'precioCompra' => 75,
                'precioVenta' => 150,
                'stock_global_actual' => 15,
                'stock_global_minimo' => 3,
                'actualizacion' => Carbon::now()->toDateString(),
                'id_sucursal' => $idSucursal,
                'categoria_id' => $categoria->id,
                'sub_categoria_id' => $subCategoria->id,
                'id_lugar' => $lugar->id,
            ],
            [
                'codigo' => 'PROD-003',
                'descripcion' => 'Producto de prueba 3',
                'precioCompra' => 100,
                'precioVenta' => 200,
                'stock_global_actual' => 10,
                'stock_global_minimo' => 2,
                'actualizacion' => Carbon::now()->toDateString(),
                'id_sucursal' => $idSucursal,
                'categoria_id' => $categoria->id,
                'sub_categoria_id' => $subCategoria->id,
                'id_lugar' => $lugar->id,
            ],
        ];

        $productoController = new ProductoController();

        foreach ($productos as $producto) {
            $request = new Request();
            $request->replace($producto);
            $productoController->store($request);
        }
    }
}
