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
        Schema::create('corte_material_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_vp_id')->constrained('materiales_venta_personalizadas')->onDelete('cascade');
            $table->foreignId('stock_varilla_id')->nullable()->constrained('stock_varillas')->onDelete('set null');
            $table->foreignId('stock_trupan_id')->nullable()->constrained('stock_trupans')->onDelete('set null');
            $table->foreignId('stock_vidrio_id')->nullable()->constrained('stock_vidrios')->onDelete('set null');
            $table->foreignId('stock_contorno_id')->nullable()->constrained('stock_contornos')->onDelete('set null');
            $table->integer('largo_corte')->comment('Medida del corte realizado (ej. 400)');
            $table->integer('ancho_corte')->nullable()->comment('Para láminas, el ancho del corte');
            $table->string('tipo_corte')->nullable()->comment('horizontal o vertical');
            $table->string('origen')->nullable()->comment('De dónde proviene, ej. Cuadro #1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corte_material_ventas');
    }
};
