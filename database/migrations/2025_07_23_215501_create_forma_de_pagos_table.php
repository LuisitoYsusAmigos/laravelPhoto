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
        Schema::create('forma_de_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

                DB::table('forma_de_pagos')->insert([
            [
                'nombre' => 'Efectivo',
            ],
            [
                'nombre' => 'Tarjeta de Crédito',
            ],
            [
                'nombre' => 'Qr',
            ],
        ]);
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forma_de_pagos');
    }
};
