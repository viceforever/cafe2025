<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'card'])->default('cash')->after('status');
            $table->enum('delivery_method', ['pickup', 'delivery'])->default('pickup')->after('payment_method');
            $table->string('delivery_address')->nullable()->after('delivery_method');
            $table->string('phone')->nullable()->after('delivery_address');
            $table->text('notes')->nullable()->after('phone');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'delivery_method', 'delivery_address', 'phone', 'notes']);
        });
    }
};
