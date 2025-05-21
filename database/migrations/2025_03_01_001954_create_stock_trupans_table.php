<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_trupans', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->integer('largo'); // Largo en cm
            $table->integer('alto'); // alto en cm
            $table->integer('precio'); // Precio en centavos
            $table->integer('stock'); // Stock disponible
            $table->boolean('contable'); // Contable (true/false)
            $table->foreignId('id_materia_prima_trupans')->constrained('materia_prima_trupans')->onDelete('cascade'); // Relación con materia_prima_varilla
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_trupans');
    }
};
