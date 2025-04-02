<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddTriggersToStockTrupans extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE TRIGGER trg_insert_stock_trupans
            AFTER INSERT ON stock_trupans
            FOR EACH ROW
            BEGIN
              IF NEW.contable = TRUE THEN
                UPDATE materia_prima_trupans
                SET stock_global_actual = stock_global_actual + NEW.stock
                WHERE id = NEW.id_materia_prima_trupan;
              END IF;
            END;

            CREATE TRIGGER trg_delete_stock_trupans
            AFTER DELETE ON stock_trupans
            FOR EACH ROW
            BEGIN
              IF OLD.contable = TRUE THEN
                UPDATE materia_prima_trupans
                SET stock_global_actual = stock_global_actual - OLD.stock
                WHERE id = OLD.id_materia_prima_trupan;
              END IF;
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_insert_stock_trupans;
            DROP TRIGGER IF EXISTS trg_delete_stock_trupans;
        ");
    }
}
