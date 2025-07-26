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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idVenta');
            $table->unsignedBigInteger('idFormaPago');
            $table->integer('monto'); // en centavos
            $table->date('fecha');
            $table->timestamps();

            // Claves foráneas
            $table->foreign('idVenta')->references('id')->on('ventas')->onDelete('cascade');
            $table->foreign('idFormaPago')->references('id')->on('forma_de_pagos')->onDelete('restrict');
        });

        DB::table('pagos')->insert([
    [
        'idVenta' => 1,
        'idFormaPago' => 1,
        'monto' => 100,
        'fecha' => '2025-07-23',
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'idVenta' => 1,
        'idFormaPago' => 2,
        'monto' => 200,
        'fecha' => '2025-07-23',
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
