<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Убираем ON UPDATE current_timestamp() из поля start_time
     */
    public function up(): void
    {
        // Изменяем поле start_time, убирая ON UPDATE current_timestamp()
        // Оставляем только DEFAULT current_timestamp() для новых записей
        DB::statement('ALTER TABLE `shifts` MODIFY `start_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     * Восстанавливаем ON UPDATE current_timestamp() (если нужно)
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `shifts` MODIFY `start_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
};
