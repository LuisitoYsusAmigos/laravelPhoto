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
        Schema::create('cajas', function (Blueprint $table) {
    $table->id();
    $table->string('detalle');
    $table->integer('total');
    $table->integer('ventas');
    $table->date('fecha');
    $table->text('observaciones')->nullable();

    $table->foreignId('id_usuario')
        ->constrained('users')
        ->onDelete('cascade');

    $table->foreignId('id_sucursal')
        ->constrained('sucursal')
        ->onDelete('restrict');

    $table->timestamps();
});
    }

    /**
     * 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
