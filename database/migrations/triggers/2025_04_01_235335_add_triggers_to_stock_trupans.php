<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddTriggersToStockTrupans extends Migration
{
    public function up(): void
    {
        // 1) Eliminamos triggers si ya existen
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_insert_stock_trupans`;');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_delete_stock_trupans`;');

        // 2) Creamos el trigger AFTER INSERT
        DB::unprepared(<<<SQL
CREATE TRIGGER `trg_insert_stock_trupans`
AFTER INSERT ON `stock_trupans`
FOR EACH ROW
BEGIN
    IF NEW.contable THEN
        UPDATE `materia_prima_trupans`
        SET stock_global_actual = stock_global_actual + NEW.stock
        WHERE id = NEW.id_materia_prima_trupans;
    END IF;
END;
SQL
        );

        // 3) Creamos el trigger AFTER DELETE (fíjate que aquí corregimos el nombre de la columna)
        DB::unprepared(<<<SQL
CREATE TRIGGER `trg_delete_stock_trupans`
AFTER DELETE ON `stock_trupans`
FOR EACH ROW
BEGIN
    IF OLD.contable THEN
        UPDATE `materia_prima_trupans`
        SET stock_global_actual = stock_global_actual - OLD.stock
        WHERE id = OLD.id_materia_prima_trupans;
    END IF;
END;
SQL
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_insert_stock_trupans`;');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_delete_stock_trupans`;');
    }
}
