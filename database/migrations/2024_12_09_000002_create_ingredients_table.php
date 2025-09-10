<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit'); // кг, л, шт и т.д.
            $table->decimal('quantity', 10, 2)->default(0); // текущий остаток
            $table->decimal('cost_per_unit', 10, 2)->default(0); // стоимость за единицу
            $table->decimal('min_quantity', 10, 2)->default(0); // минимальный остаток для предупреждения
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ingredients');
    }
};
