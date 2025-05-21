<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materia_prima_trupans', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->integer('precioCompra');
            $table->integer('precioVenta');
            $table->integer('alto'); // Alto en cm
            $table->integer('largo'); // Largo en cm
            $table->integer('grosor');
            $table->decimal('factor_desperdicio', 5, 2);
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('sub_categoria_id')->constrained('sub_categorias')->onDelete('cascade');
            $table->integer('stock_global_actual');
            $table->integer('stock_global_minimo');
            $table->foreignId('id_sucursal')->constrained('sucursal')->onDelete('cascade');
            $table->string('imagen')->nullable();
            $table->timestamps();
        });

        // Datos de ejemplo
        DB::table('materia_prima_trupans')->insert([
            [
                'descripcion' => 'Trupán blanco 18mm',
                'precioCompra' => 200,
                'precioVenta' => 300,
                'alto' => 120,
                'largo' => 240,
                'grosor' => 18,
                'factor_desperdicio' => 1.05,
                'categoria_id' => 1,
                'sub_categoria_id' => 1,
                'stock_global_actual' => 200,
                'stock_global_minimo' => 50,
                'id_sucursal' => 1,
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Trupán MDF 15mm',
                'precioCompra' => 180,
                'precioVenta' => 280,
                'alto' => 120,
                'largo' => 240,
                'grosor' => 15,
                'factor_desperdicio' => 1.03,
                'categoria_id' => 1,
                'sub_categoria_id' => 1,
                'stock_global_actual' => 180,
                'stock_global_minimo' => 40,
                'id_sucursal' => 1,
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Trupán crudo 12mm',
                'precioCompra' => 160,
                'precioVenta' => 240,
                'alto' => 120,
                'largo' => 240,
                'grosor' => 12,
                'factor_desperdicio' => 1.02,
                'categoria_id' => 1,
                'sub_categoria_id' => 1,
                'stock_global_actual' => 160,
                'stock_global_minimo' => 30,
                'id_sucursal' => 1,
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Trupán laminado 10mm',
                'precioCompra' => 140,
                'precioVenta' => 210,
                'alto' => 120,
                'largo' => 240,
                'grosor' => 10,
                'factor_desperdicio' => 1.04,
                'categoria_id' => 1,
                'sub_categoria_id' => 1,
                'stock_global_actual' => 140,
                'stock_global_minimo' => 25,
                'id_sucursal' => 1,
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Trupán impermeable 20mm',
                'precioCompra' => 100,
                'precioVenta' => 150,
                'alto' => 120,
                'largo' => 240,
                'grosor' => 20,
                'factor_desperdicio' => 1.06,
                'categoria_id' => 1,
                'sub_categoria_id' => 1,
                'stock_global_actual' => 100,
                'stock_global_minimo' => 20,
                'id_sucursal' => 1,
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_prima_trupans');
    }
};
