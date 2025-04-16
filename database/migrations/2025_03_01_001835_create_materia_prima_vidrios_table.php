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
        Schema::create('materia_prima_vidrios', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->string('descripcion'); // Descripción del vidrio
            $table->integer('grosor'); // Grosor en unidades (ejemplo: mm)
            $table->decimal('factor_desperdicio', 5, 2); // Factor de desperdicio con 2 decimales
            $table->string('categoria'); // Categoría del vidrio
            $table->string('sub_categoria'); // Subcategoría
            $table->integer('stock_global_actual'); // Stock actual
            $table->integer('stock_global_minimo'); // Stock mínimo
            $table->foreignId('id_sucursal')->constrained('sucursal')->onDelete('cascade'); // Relación con la tabla sucursales
            $table->timestamps(); // created_at y updated_at
        });

        // Insertar 5 registros de ejemplo
        DB::table('materia_prima_vidrios')->insert([
            [
                'descripcion' => 'Vidrio templado 6mm',
                'grosor' => 6,
                'factor_desperdicio' => 1.07,
                'categoria' => 'Templado',
                'sub_categoria' => 'Transparente',
                'stock_global_actual' => 90,
                'stock_global_minimo' => 30,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Vidrio esmerilado 8mm',
                'grosor' => 8,
                'factor_desperdicio' => 1.06,
                'categoria' => 'Decorativo',
                'sub_categoria' => 'Esmerilado',
                'stock_global_actual' => 75,
                'stock_global_minimo' => 25,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Vidrio laminado 10mm',
                'grosor' => 10,
                'factor_desperdicio' => 1.08,
                'categoria' => 'Laminado',
                'sub_categoria' => 'Seguridad',
                'stock_global_actual' => 60,
                'stock_global_minimo' => 20,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Vidrio float 4mm',
                'grosor' => 4,
                'factor_desperdicio' => 1.04,
                'categoria' => 'Económico',
                'sub_categoria' => 'Float',
                'stock_global_actual' => 110,
                'stock_global_minimo' => 35,
                'id_sucursal' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'Vidrio doble acristalamiento 12mm',
                'grosor' => 12,
                'factor_desperdicio' => 1.10,
                'categoria' => 'Térmico',
                'sub_categoria' => 'Doble acristalamiento',
                'stock_global_actual' => 45,
                'stock_global_minimo' => 15,
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
        Schema::dropIfExists('materia_prima_vidrios');
    }
};
