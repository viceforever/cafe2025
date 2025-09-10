<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['client', 'manager', 'admin'])->default('client')->after('password');
        });

        DB::statement("UPDATE users SET role = CASE WHEN is_admin = 1 THEN 'admin' ELSE 'client' END");
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->dropColumn('role');
        });
    }
};
