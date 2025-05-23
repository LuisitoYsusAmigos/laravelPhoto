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
        Schema::create('stock_varillas', function (Blueprint $table) {
            $table->id();
            $table->integer('largo');
            $table->integer('precio');
            $table->integer('stock');
            $table->boolean('contable');
            $table->foreignId('id_materia_prima_varilla')->constrained('materia_prima_varillas')->onDelete('cascade');
            $table->timestamps();
        });

        // Trigger AFTER INSERT
        DB::unprepared('
            CREATE TRIGGER trg_after_insert_stock_varillas
            AFTER INSERT ON stock_varillas
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    UPDATE materia_prima_varillas
                    SET 
                        stock_global_actual = stock_global_actual + NEW.stock,
                        precioCompra = NEW.precio
                    WHERE id = NEW.id_materia_prima_varilla;
                END IF;
            END
        ');

        // Trigger AFTER UPDATE
        DB::unprepared('
            CREATE TRIGGER trg_after_update_stock_varillas
            AFTER UPDATE ON stock_varillas
            FOR EACH ROW
            BEGIN
                IF NEW.contable = 1 THEN
                    IF NEW.id_materia_prima_varilla = OLD.id_materia_prima_varilla THEN
                        UPDATE materia_prima_varillas
                        SET 
                            stock_global_actual = stock_global_actual + (NEW.stock - OLD.stock),
                            precioCompra = NEW.precio
                        WHERE id = NEW.id_materia_prima_varilla;
                    END IF;
                END IF;
            END
        ');

        // Trigger AFTER DELETE
        DB::unprepared('
            CREATE TRIGGER trg_after_delete_stock_varillas
            AFTER DELETE ON stock_varillas
            FOR EACH ROW
            BEGIN
                IF OLD.contable = 1 THEN
                    UPDATE materia_prima_varillas
                    SET 
                        stock_global_actual = stock_global_actual - OLD.stock
                    WHERE id = OLD.id_materia_prima_varilla;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_insert_stock_varillas');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_update_stock_varillas');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_delete_stock_varillas');

        Schema::dropIfExists('stock_varillas');
    }
};
