<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetalleVentaProductosTable extends Migration
{
    public function up()
    {
        Schema::create('detalle_venta_productos', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad');
            $table->integer('precio'); // Ahora lo defines manualmente en el controlador

            $table->unsignedBigInteger('idVenta');
            $table->unsignedBigInteger('idProducto');
            $table->unsignedBigInteger('id_stock_producto'); // NUEVO: lote

            $table->foreign('idVenta')->references('id')->on('ventas')->onDelete('cascade');
            $table->foreign('idProducto')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('id_stock_producto')->references('id')->on('stock_productos')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_venta_productos');
    }
}
