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
            $table->integer('monto'); 
            $table->date('fecha');
            $table->timestamps();

            // Claves foráneas
            $table->foreign('idVenta')->references('id')->on('ventas')->onDelete('cascade');
            $table->foreign('idFormaPago')->references('id')->on('forma_de_pagos')->onDelete('restrict');
        });



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
