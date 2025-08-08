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
        Schema::create('materiales_venta_personalizadas', function (Blueprint $table) {
            $table->id();

            // Relaciones opcionales (pueden ser null)
            $table->foreignId('stock_contorno_id')->nullable()->constrained('stock_contornos')->onDelete('cascade');
            $table->foreignId('stock_trupan_id')->nullable()->constrained('stock_trupans')->onDelete('cascade');
            $table->foreignId('stock_vidrio_id')->nullable()->constrained('stock_vidrios')->onDelete('cascade');
            $table->foreignId('stock_varilla_id')->nullable()->constrained('stock_varillas')->onDelete('cascade');
            $table->integer('cantidad');
            $table->integer('precio_unitario')->nullable(); // Precio unitario opcional;
            $table->foreignId('detalleVP_id')->constrained('detalle_venta_personalizadas')->onDelete('cascade'); // Obligatorio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiales_venta_personalizadas');
    }
};
