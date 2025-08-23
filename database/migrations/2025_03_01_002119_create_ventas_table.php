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
            $table->dateTime('fechaEntrega')->nullable(); // <-- Nuevo campo

            $table->foreignId('idCliente')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('idSucursal')->constrained('sucursal')->onDelete('cascade');
            $table->foreignId('idUsuario')->constrained('users')->onDelete('cascade');
            

            $table->timestamps();
        });


    }

    public function down()
    {
        Schema::dropIfExists('ventas');
    }
}
