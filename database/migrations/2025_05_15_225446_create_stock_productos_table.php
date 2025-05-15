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
        Schema::create('stock_productos', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->integer('stock'); // Stock disponible
            $table->integer('precio'); // Precio en centavos
            $table->boolean('contable'); // Contable (true/false)
            $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade'); // Relación con materia_prima_varilla
            $table->timestamps(); // created_at y updated_at
        });
            DB::table('stock_productos')->insert([
            [
                'stock'=> 10,
                'precio'=> 100,
                'contable'=> true,
                'id_producto'=> 1,
                
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_productos');
    }
};
