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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->string('descripcion');
            $table->integer('precioCompra');
            $table->integer('precioVenta');
            $table->integer('stock_global_actual');
            $table->integer('stock_global_minimo');
            $table->date('actualizacion');
            $table->foreignId('id_sucursal')->constrained('sucursal')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('id_lugar')->constrained('lugars')->onDelete('cascade');
            $table->foreignId('sub_categoria_id')->nullable()->constrained('sub_categorias')->onDelete('cascade');      
            $table->string('imagen')->nullable();      
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
