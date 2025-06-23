<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_contornos', function (Blueprint $table) {
            $table->id();
            $table->integer('largo');
            $table->integer('alto');
            $table->integer('stock');
            $table->integer('precio');
            $table->boolean('contable');
            $table->foreignId('id_materia_prima_contorno')->constrained('materia_prima_contornos')->onDelete('cascade');
            $table->timestamps();
        });

        // Trigger AFTER INSERT
        DB::unprepared('
            CREATE TRIGGER trg_after_insert_stock_contornos
            AFTER INSERT ON stock_contornos
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    UPDATE materia_prima_contornos
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_contornos
                            WHERE contable = 1 AND id_materia_prima_contorno = NEW.id_materia_prima_contorno
                        ),
                        precioCompra = NEW.precio
                    WHERE id = NEW.id_materia_prima_contorno;
                END IF;
            END
        ');

        // Trigger AFTER UPDATE
        DB::unprepared('
            CREATE TRIGGER trg_after_update_stock_contornos
            AFTER UPDATE ON stock_contornos
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    UPDATE materia_prima_contornos
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_contornos
                            WHERE contable = 1 AND id_materia_prima_contorno = NEW.id_materia_prima_contorno
                        ),
                        precioCompra = NEW.precio
                    WHERE id = NEW.id_materia_prima_contorno;
                END IF;

                IF OLD.contable = 1 AND (OLD.id_materia_prima_contorno != NEW.id_materia_prima_contorno OR NEW.contable = 0) THEN
                    UPDATE materia_prima_contornos
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_contornos
                            WHERE contable = 1 AND id_materia_prima_contorno = OLD.id_materia_prima_contorno
                        )
                    WHERE id = OLD.id_materia_prima_contorno;
                END IF;
            END
        ');

        // Trigger AFTER DELETE
        DB::unprepared('
            CREATE TRIGGER trg_after_delete_stock_contornos
            AFTER DELETE ON stock_contornos
            FOR EACH ROW
            BEGIN
                IF OLD.contable = 1 THEN
                    UPDATE materia_prima_contornos
                    SET 
                        stock_global_actual = (
                            SELECT IFNULL(SUM(stock), 0)
                            FROM stock_contornos
                            WHERE contable = 1 AND id_materia_prima_contorno = OLD.id_materia_prima_contorno
                        )
                    WHERE id = OLD.id_materia_prima_contorno;
                END IF;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_insert_stock_contornos');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_update_stock_contornos');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_delete_stock_contornos');

        Schema::dropIfExists('stock_contornos');
    }
};
