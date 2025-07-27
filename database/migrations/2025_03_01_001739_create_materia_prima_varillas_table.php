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

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_prima_varillas');
    }
};
