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
        Schema::create('materia_prima_varillas', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->string('descripcion'); // Descripción de la varilla
            $table->integer('grosor'); // Grosor en unidades (ejemplo: mm)
            $table->integer('ancho'); // Ancho en unidades (ejemplo: mm)
            $table->decimal('factor_desperdicio', 5, 2); // Factor de desperdicio con 2 decimales
            $table->string('categoria'); // Categoría de la varilla
            $table->string('sub_categoria'); // Subcategoría
            $table->integer('stock_global_actual'); // Stock actual
            $table->integer('stock_global_minimo'); // Stock mínimo
            $table->foreignId('id_sucursal')->constrained('sucursal')->onDelete('cascade'); // Relación con la tabla sucursales
            $table->timestamps(); // created_at y updated_at
        });

        // Insertar 5 registros de ejemplo
        DB::table('materia_prima_varillas')->insert([
            [
                'descripcion' => 'Varilla 1/2"',
                'grosor' => 12,
                'ancho' => 10,
                'factor_desperdicio' => 1.05,
                'categoria' => 'Metálica',
                'sub_categoria' => 'Acero',
                'stock_global_actual' => 150,
                'stock_global_minimo' => 50,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Varilla 3/8"',
                'grosor' => 10,
                'ancho' => 8,
                'factor_desperdicio' => 1.03,
                'categoria' => 'Metálica',
                'sub_categoria' => 'Hierro',
                'stock_global_actual' => 100,
                'stock_global_minimo' => 40,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Varilla galvanizada',
                'grosor' => 15,
                'ancho' => 12,
                'factor_desperdicio' => 1.08,
                'categoria' => 'Galvanizada',
                'sub_categoria' => 'Zinc',
                'stock_global_actual' => 120,
                'stock_global_minimo' => 30,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Varilla reforzada',
                'grosor' => 16,
                'ancho' => 14,
                'factor_desperdicio' => 1.10,
                'categoria' => 'Metálica',
                'sub_categoria' => 'Reforzada',
                'stock_global_actual' => 90,
                'stock_global_minimo' => 25,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Varilla de aluminio',
                'grosor' => 8,
                'ancho' => 6,
                'factor_desperdicio' => 1.02,
                'categoria' => 'Ligera',
                'sub_categoria' => 'Aluminio',
                'stock_global_actual' => 80,
                'stock_global_minimo' => 20,
                'id_sucursal' => 1,
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
        Schema::dropIfExists('materia_prima_varillas');
    }
};
