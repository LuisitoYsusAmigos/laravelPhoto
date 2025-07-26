<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateVentasTable extends Migration
{
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->integer('precioProducto')->default(0);
            $table->integer('precioPerzonalizado')->default(0);
            $table->integer('precioTotal')->default(0);
            $table->integer('saldo')->default(0);

            $table->boolean('recogido')->default(false);
            $table->dateTime('fecha');

            $table->foreignId('idCliente')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('idSucursal')->constrained('sucursal')->onDelete('cascade');

            

            $table->timestamps();
        });

        // Inserción automática de una venta con cliente 1 y sucursal 1
        DB::table('ventas')->insert([
            'saldo' => 0,
            'recogido' => false,
            'fecha' => now(),
            'idCliente' => 1,
            'idSucursal' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'precioTotal' => 1000,
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('ventas');
    }
}
