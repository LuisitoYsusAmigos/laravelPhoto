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
            $table->id(); // ID autoincremental
            $table->string('descripcion'); // Descripción del trupan
            $table->integer('grosor'); // Grosor en unidades (ejemplo: mm)
            $table->decimal('factor_desperdicio', 5, 2); // Factor de desperdicio con 2 decimales
            $table->string('categoria'); // Categoría del trupan
            $table->string('sub_categoria'); // Subcategoría
            $table->integer('stock_global_actual'); // Stock actual
            $table->integer('stock_global_minimo'); // Stock mínimo
            $table->foreignId('id_sucursal')->constrained('sucursal')->onDelete('cascade'); // Relación con la tabla sucursales
            $table->timestamps(); // created_at y updated_at
        });

        // Insertar 5 registros de ejemplo
        DB::table('materia_prima_trupans')->insert([
            [
                'descripcion' => 'Trupán blanco 18mm',
                'grosor' => 18,
                'factor_desperdicio' => 1.05,
                'categoria' => 'Blanco',
                'sub_categoria' => 'Melamina',
                'stock_global_actual' => 200,
                'stock_global_minimo' => 50,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Trupán MDF 15mm',
                'grosor' => 15,
                'factor_desperdicio' => 1.03,
                'categoria' => 'MDF',
                'sub_categoria' => 'Natural',
                'stock_global_actual' => 180,
                'stock_global_minimo' => 40,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Trupán crudo 12mm',
                'grosor' => 12,
                'factor_desperdicio' => 1.02,
                'categoria' => 'Crudo',
                'sub_categoria' => 'Standard',
                'stock_global_actual' => 160,
                'stock_global_minimo' => 30,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Trupán laminado 10mm',
                'grosor' => 10,
                'factor_desperdicio' => 1.04,
                'categoria' => 'Laminado',
                'sub_categoria' => 'Decorativo',
                'stock_global_actual' => 140,
                'stock_global_minimo' => 25,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Trupán impermeable 20mm',
                'grosor' => 20,
                'factor_desperdicio' => 1.06,
                'categoria' => 'Especial',
                'sub_categoria' => 'Impermeable',
                'stock_global_actual' => 100,
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
        Schema::dropIfExists('materia_prima_trupans');
    }
};
