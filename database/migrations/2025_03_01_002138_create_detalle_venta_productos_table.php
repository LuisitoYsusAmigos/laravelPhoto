<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDetalleVentaProductosTable extends Migration
{
    public function up()
    {
        Schema::create('detalle_venta_productos', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad');
            $table->integer('precio'); // será calculado por el trigger

            $table->unsignedBigInteger('idVenta');
            $table->unsignedBigInteger('idProducto');

            $table->foreign('idVenta')->references('id')->on('ventas')->onDelete('cascade');
            $table->foreign('idProducto')->references('id')->on('productos')->onDelete('cascade');

            $table->timestamps();
        });

        // Crear el trigger para calcular precio automáticamente
        DB::unprepared("
            CREATE TRIGGER calcular_precio_detalle_venta
            BEFORE INSERT ON detalle_venta_productos
            FOR EACH ROW
            BEGIN
                DECLARE precio_unitario INT;
                SELECT precioVenta INTO precio_unitario FROM productos WHERE id = NEW.idProducto;
                SET NEW.precio = NEW.cantidad * precio_unitario;
            END
        ");

        // Insertar un registro automático
        DB::table('detalle_venta_productos')->insert([
            'cantidad' => 2,
            'idVenta' => 1,
            'idProducto' => 1,
            'created_at' => now(),
            'updated_at' => now()
            // No colocamos 'precio', lo calcula el trigger
        ]);
    }

    public function down()
    {
        // Eliminar trigger antes de eliminar la tabla
        DB::unprepared("DROP TRIGGER IF EXISTS calcular_precio_detalle_venta");
        Schema::dropIfExists('detalle_venta_productos');
    }
}
