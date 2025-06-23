<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia_prima_contornos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->string('descripcion');
            $table->integer('precioCompra');
            $table->integer('precioVenta');
            $table->integer('alto');
            $table->integer('largo');
            $table->decimal('factor_desperdicio', 5, 2);
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('id_lugar')->constrained('lugars')->onDelete('cascade');
            $table->foreignId('sub_categoria_id')->nullable()->constrained('sub_categorias')->onDelete('cascade');
            $table->integer('stock_global_actual');
            $table->integer('stock_global_minimo');
            $table->foreignId('id_sucursal')->constrained('sucursal')->onDelete('cascade');
            $table->string('imagen')->nullable();
            $table->timestamps();
        });

        DB::table('materia_prima_contornos')->insert([
            [
                'codigo' => 'MPC001',
                'descripcion' => 'Contorno decorativo blanco',
                'precioCompra' => 80,
                'precioVenta' => 120,
                'alto' => 50,
                'largo' => 200,
                'factor_desperdicio' => 1.05,
                'categoria_id' => 1,
                'id_lugar' => 1,
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

    public function down(): void
    {
        Schema::dropIfExists('materia_prima_contornos');
    }
};
