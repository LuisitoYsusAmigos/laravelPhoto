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
        Schema::create('stock_vidrios', function (Blueprint $table) {
            $table->id();
            $table->integer('largo');
            $table->integer('alto');
            $table->integer('stock');
            $table->integer('precio');
            $table->boolean('contable');
            $table->foreignId('id_materia_prima_vidrio')->constrained('materia_prima_vidrios')->onDelete('cascade');
            $table->timestamps();
        });

        // Trigger AFTER INSERT
        DB::unprepared('
            CREATE TRIGGER trg_after_insert_stock_vidrios
            AFTER INSERT ON stock_vidrios
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    UPDATE materia_prima_vidrios
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_vidrios
                            WHERE contable = 1 AND id_materia_prima_vidrio = NEW.id_materia_prima_vidrio
                        ),
                        precioCompra = NEW.precio
                    WHERE id = NEW.id_materia_prima_vidrio;
                END IF;
            END
        ');

        // Trigger AFTER UPDATE
        DB::unprepared('
            CREATE TRIGGER trg_after_update_stock_vidrios
            AFTER UPDATE ON stock_vidrios
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    UPDATE materia_prima_vidrios
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_vidrios
                            WHERE contable = 1 AND id_materia_prima_vidrio = NEW.id_materia_prima_vidrio
                        ),
                        precioCompra = NEW.precio
                    WHERE id = NEW.id_materia_prima_vidrio;
                END IF;

                IF OLD.contable = 1 AND (OLD.id_materia_prima_vidrio != NEW.id_materia_prima_vidrio OR NEW.contable = 0) THEN
                    UPDATE materia_prima_vidrios
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_vidrios
                            WHERE contable = 1 AND id_materia_prima_vidrio = OLD.id_materia_prima_vidrio
                        )
                    WHERE id = OLD.id_materia_prima_vidrio;
                END IF;
            END
        ');

        // Trigger AFTER DELETE
        DB::unprepared('
            CREATE TRIGGER trg_after_delete_stock_vidrios
            AFTER DELETE ON stock_vidrios
            FOR EACH ROW
            BEGIN
                IF OLD.contable = 1 THEN
                    UPDATE materia_prima_vidrios
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_vidrios
                            WHERE contable = 1 AND id_materia_prima_vidrio = OLD.id_materia_prima_vidrio
                        )
                    WHERE id = OLD.id_materia_prima_vidrio;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar los triggers
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_insert_stock_vidrios');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_update_stock_vidrios');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_delete_stock_vidrios');

        Schema::dropIfExists('stock_vidrios');
    }
};
