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
            $table->string('codigo')->nullable();
            $table->string('descripcion'); // Descripción de la varilla
            $table->integer('precioCompra');
            $table->integer('precioVenta');
            $table->integer('largo'); // largo en unidades (ejemplo: mm)
            $table->integer('grosor'); // Grosor en unidades (ejemplo: mm)
            $table->integer('alto'); // alto en unidades (ejemplo: mm)
            $table->decimal('factor_desperdicio', 5, 2); // Factor de desperdicio con 2 decimales
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('id_lugar')->constrained('lugars')->onDelete('cascade');
            $table->foreignId('sub_categoria_id')->nullable()->constrained('sub_categorias')->onDelete('cascade'); // ✅
 // Relación con la tabla subcategorías, puede ser nulo
            $table->integer('stock_global_actual'); // Stock actual
            $table->integer('stock_global_minimo'); // Stock mínimo
            $table->foreignId('id_sucursal')->constrained('sucursal')->onDelete('cascade'); // Relación con la tabla sucursales
            $table->string('imagen')->nullable();     
            $table->timestamps(); // created_at y updated_at
        });

        // Insertar 5 registros de ejemplo
        DB::table('materia_prima_varillas')->insert([
            [
                'codigo' => 'MV001',
                'descripcion' => 'Varilla 1/2"',
                'precioCompra' => 100,
                'precioVenta' => 150,
                'largo' => 300,
                'grosor' => 12,
                'alto' => 10,
                'factor_desperdicio' => 1.05,
                'categoria_id' => 1,
                'sub_categoria_id' => 1,
                'stock_global_actual' => 150,
                'stock_global_minimo' => 50,
                'id_sucursal' => 1,
                'id_lugar' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
       
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
