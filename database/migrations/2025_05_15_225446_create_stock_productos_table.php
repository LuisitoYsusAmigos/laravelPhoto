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
        Schema::create('stock_productos', function (Blueprint $table) {
            $table->id();
            $table->integer('stock');
            $table->integer('precio');
            $table->boolean('contable');
            $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade');
            $table->timestamps();
        });

        // Datos de prueba
        DB::table('stock_productos')->insert([
            [
                'stock' => 10,
                'precio' => 100,
                'contable' => true,
                'id_producto' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Trigger AFTER INSERT
        DB::unprepared('
            CREATE TRIGGER trg_after_insert_stock_productos
            AFTER INSERT ON stock_productos
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    UPDATE productos
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_productos
                            WHERE contable = 1 AND id_producto = NEW.id_producto
                        ),
                        precioVenta = NEW.precio
                    WHERE id = NEW.id_producto;
                END IF;
            END
        ');

        // Trigger AFTER UPDATE
        DB::unprepared('
            CREATE TRIGGER trg_after_update_stock_productos
            AFTER UPDATE ON stock_productos
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    UPDATE productos
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_productos
                            WHERE contable = 1 AND id_producto = NEW.id_producto
                        ),
                        precioVenta = NEW.precio
                    WHERE id = NEW.id_producto;
                END IF;

                IF OLD.contable = 1 AND (OLD.id_producto != NEW.id_producto OR NEW.contable = 0) THEN
                    UPDATE productos
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_productos
                            WHERE contable = 1 AND id_producto = OLD.id_producto
                        )
                    WHERE id = OLD.id_producto;
                END IF;
            END
        ');

        // Trigger AFTER DELETE
        DB::unprepared('
            CREATE TRIGGER trg_after_delete_stock_productos
            AFTER DELETE ON stock_productos
            FOR EACH ROW
            BEGIN
                IF OLD.contable = 1 THEN
                    UPDATE productos
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_productos
                            WHERE contable = 1 AND id_producto = OLD.id_producto
                        )
                    WHERE id = OLD.id_producto;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_insert_stock_productos');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_update_stock_productos');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_delete_stock_productos');

        Schema::dropIfExists('stock_productos');
    }
};
