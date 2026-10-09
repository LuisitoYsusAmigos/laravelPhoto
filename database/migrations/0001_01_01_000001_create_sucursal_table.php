<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Importar DB

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sucursal', function (Blueprint $table) {
            $table->id();
            $table->string('lugar');
            $table->string('nombre_sucursal')->nullable();
            $table->string('gerente')->nullable();
            $table->string('direccion')->nullable();
            $table->string('contactos')->nullable();
            $table->string('correo')->nullable();
            $table->timestamps();
        });

        // Insertar datos iniciales
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursal');
    }
};
