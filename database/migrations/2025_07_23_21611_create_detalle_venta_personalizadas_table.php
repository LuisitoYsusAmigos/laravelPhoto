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
    Schema::create('detalle_venta_personalizadas', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
        
        // Campos obligatorios (sin nullable)
        $table->integer('lado_a');  // Obligatorio
        $table->integer('lado_b');  // Obligatorio
        
        // Foreign keys opcionales (pueden ser NULL)
        $table->foreignId('id_materia_prima_varillas')->nullable()->constrained('materia_prima_varillas')->onDelete('cascade');
        $table->foreignId('id_materia_prima_trupans')->nullable()->constrained('materia_prima_trupans')->onDelete('cascade');
        $table->foreignId('id_materia_prima_vidrios')->nullable()->constrained('materia_prima_vidrios')->onDelete('cascade');
        $table->foreignId('id_materia_prima_contornos')->nullable()->constrained('materia_prima_contornos')->onDelete('cascade');
        
        // Foreign key obligatoria
        $table->foreignId('id_venta')->constrained('ventas')->onDelete('cascade'); // Obligatorio
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_venta_personalizadas');
    }
};
