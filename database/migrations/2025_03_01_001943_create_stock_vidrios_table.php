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
        Schema::create('stock_vidrios', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->integer('largo'); // Largo en cm
            $table->integer('alto'); // Alto en cm
            $table->integer('stock'); // Stock disponible
            $table->integer('precio'); // Precio en centavos
            $table->boolean('contable'); // Contable (true/false)
            $table->foreignId('id_materia_prima_vidrio')->constrained('materia_prima_vidrios')->onDelete('cascade'); // Relación con materia_prima_vidrio
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_vidrios');
    }
};
