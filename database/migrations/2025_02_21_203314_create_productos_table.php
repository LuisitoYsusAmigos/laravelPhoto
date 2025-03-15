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
            $table->string('descripcion');
            $table->integer('precioCompra');
            $table->integer('precioVenta');
            $table->integer('stock');
            $table->integer('stockMin');
            $table->date('actualizacion');
            $table->foreignId('sucursal_id')->constrained('sucursal')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('sub_categoria_id')->constrained('sub_categorias')->onDelete('cascade');      
            $table->string('imagen')->nullable();      
            $table->timestamps();
        });
        DB::table('productos')->insert([
            [
                'descripcion'=> 'Producto de ejemplo',
                'precioCompra'=> 100,
                'precioVenta'=> 150,
                'stock'=> 50,
                'stockMin'=> 10,
                'actualizacion'=> '2024-03-13',
                'sucursal_id'=> 1,
                'categoria_id'=> 1,
                'sub_categoria_id'=> 1
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
