<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddTriggersToStockVidrios extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE TRIGGER trg_insert_stock_vidrios
            AFTER INSERT ON stock_vidrios
            FOR EACH ROW
            BEGIN
              IF NEW.contable = TRUE THEN
                UPDATE materia_prima_vidrios
                SET stock_global_actual = stock_global_actual + NEW.stock
                WHERE id = NEW.id_materia_prima_vidrio;
              END IF;
            END;

            CREATE TRIGGER trg_delete_stock_vidrios
            AFTER DELETE ON stock_vidrios
            FOR EACH ROW
            BEGIN
              IF OLD.contable = TRUE THEN
                UPDATE materia_prima_vidrios
                SET stock_global_actual = stock_global_actual - OLD.stock
                WHERE id = OLD.id_materia_prima_vidrio;
              END IF;
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_insert_stock_vidrios;
            DROP TRIGGER IF EXISTS trg_delete_stock_vidrios;
        ");
    }
}
