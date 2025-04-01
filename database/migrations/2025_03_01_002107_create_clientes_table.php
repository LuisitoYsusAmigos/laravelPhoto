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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('ci')->unique();
            $table->string('nombre');
            $table->string('apellido');
            $table->date('fechaNacimiento');
            $table->string('telefono');
            $table->string('direccion');
            $table->string('email')->unique();
            $table->timestamps();
        });

        // Insertar datos de ejemplo
        DB::table('clientes')->insert([
            [
                'ci' => '1234567',
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'fechaNacimiento' => '1990-01-01',
                'telefono' => '12345678',
                'direccion' => 'Calle Principal #123',
                'email' => 'juan.perez@example.com',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'ci' => '7654321',
                'nombre' => 'María',
                'apellido' => 'González',
                'fechaNacimiento' => '1985-05-15',
                'telefono' => '87654321',
                'direccion' => 'Avenida Central #456',
                'email' => 'maria.gonzalez@example.com',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
