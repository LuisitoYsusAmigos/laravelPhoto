<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddTriggersToStockVarillas extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE TRIGGER trg_insert_stock_varillas
            AFTER INSERT ON stock_varillas
            FOR EACH ROW
            BEGIN
              IF NEW.contable = TRUE THEN
                UPDATE materia_prima_varillas
                SET stock_global_actual = stock_global_actual + NEW.stock
                WHERE id = NEW.id_materia_prima_varilla;
              END IF;
            END;

            CREATE TRIGGER trg_delete_stock_varillas
            AFTER DELETE ON stock_varillas
            FOR EACH ROW
            BEGIN
              IF OLD.contable = TRUE THEN
                UPDATE materia_prima_varillas
                SET stock_global_actual = stock_global_actual - OLD.stock
                WHERE id = OLD.id_materia_prima_varilla;
              END IF;
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_insert_stock_varillas;
            DROP TRIGGER IF EXISTS trg_delete_stock_varillas;
        ");
    }
}
