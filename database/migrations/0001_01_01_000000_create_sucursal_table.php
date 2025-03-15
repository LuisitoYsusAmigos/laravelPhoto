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
            $table->timestamps();
        });

        // Insertar datos iniciales
        DB::table('sucursal')->insert([
            ['lugar' => 'Tarija', 'created_at' => now(), 'updated_at' => now()],
            ['lugar' => 'Sucursal Norte', 'created_at' => now(), 'updated_at' => now()],
            ['lugar' => 'Sucursal Sur', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursal');
    }
};
