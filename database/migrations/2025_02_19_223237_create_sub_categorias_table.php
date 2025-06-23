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
        Schema::create('sub_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('id_categoria')->constrained('categorias')->onDelete('cascade');
            $table->timestamps();
        });

        DB::table('sub_categorias')->insert([
            [
                'nombre' => 'fotografia',
                'id_categoria' => 1
            ],
            [
                'nombre' => 'sub de varilla',
                'id_categoria' => 2
            ],
            [
                'nombre' => 'sub de trupan',
                'id_categoria' => 2
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categorias');
    }
};
