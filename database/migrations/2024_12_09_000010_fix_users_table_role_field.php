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
        // Проверяем, существует ли поле role
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('client')->after('password');
            });
        }

        // Если поле is_admin существует, переносим данные и удаляем его
        if (Schema::hasColumn('users', 'is_admin')) {
            // Обновляем роли на основе is_admin
            DB::table('users')
                ->where('is_admin', 1)
                ->update(['role' => 'admin']);
            
            DB::table('users')
                ->where('is_admin', 0)
                ->orWhereNull('is_admin')
                ->update(['role' => 'client']);

            // Удаляем старое поле
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }

        // Устанавливаем роль 'client' для всех пользователей без роли
        DB::table('users')
            ->whereNull('role')
            ->orWhere('role', '')
            ->update(['role' => 'client']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            // Добавляем обратно поле is_admin
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false)->after('password');
            });

            // Переносим данные обратно
            DB::table('users')
                ->where('role', 'admin')
                ->update(['is_admin' => 1]);
            
            DB::table('users')
                ->where('role', '!=', 'admin')
                ->update(['is_admin' => 0]);

            // Удаляем поле role
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
