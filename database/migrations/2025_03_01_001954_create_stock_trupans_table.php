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
        Schema::create('stock_trupans', function (Blueprint $table) {
            $table->id();
            $table->integer('largo');
            $table->integer('alto');
            $table->integer('precio');
            $table->integer('stock');
            $table->boolean('contable');
            $table->foreignId('id_materia_prima_trupans')->constrained('materia_prima_trupans')->onDelete('cascade');
            $table->timestamps();
        });

        // Trigger AFTER INSERT
        DB::unprepared('
            CREATE TRIGGER trg_after_insert_stock_trupans
            AFTER INSERT ON stock_trupans
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    UPDATE materia_prima_trupans
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_trupans
                            WHERE contable = 1 AND id_materia_prima_trupans = NEW.id_materia_prima_trupans
                        ),
                        precioCompra = NEW.precio
                    WHERE id = NEW.id_materia_prima_trupans;
                END IF;
            END
        ');

        // Trigger AFTER UPDATE
        DB::unprepared('
            CREATE TRIGGER trg_after_update_stock_trupans
            AFTER UPDATE ON stock_trupans
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    UPDATE materia_prima_trupans
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_trupans
                            WHERE contable = 1 AND id_materia_prima_trupans = NEW.id_materia_prima_trupans
                        ),
                        precioCompra = NEW.precio
                    WHERE id = NEW.id_materia_prima_trupans;
                END IF;

                IF OLD.contable = 1 AND (OLD.id_materia_prima_trupans != NEW.id_materia_prima_trupans OR NEW.contable = 0) THEN
                    UPDATE materia_prima_trupans
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_trupans
                            WHERE contable = 1 AND id_materia_prima_trupans = OLD.id_materia_prima_trupans
                        )
                    WHERE id = OLD.id_materia_prima_trupans;
                END IF;
            END
        ');

        // Trigger AFTER DELETE
        DB::unprepared('
            CREATE TRIGGER trg_after_delete_stock_trupans
            AFTER DELETE ON stock_trupans
            FOR EACH ROW
            BEGIN
                IF OLD.contable = 1 THEN
                    UPDATE materia_prima_trupans
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_trupans
                            WHERE contable = 1 AND id_materia_prima_trupans = OLD.id_materia_prima_trupans
                        )
                    WHERE id = OLD.id_materia_prima_trupans;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_insert_stock_trupans');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_update_stock_trupans');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_delete_stock_trupans');

        Schema::dropIfExists('stock_trupans');
    }
};
