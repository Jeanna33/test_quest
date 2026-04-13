<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            // даты
            $table->date('date')->nullable();
            $table->date('last_change_date')->nullable();

            // товар
            $table->string('supplier_article')->nullable();
            $table->string('tech_size')->nullable();

            // идентификаторы
            $table->bigInteger('barcode')->nullable();
            $table->bigInteger('nm_id')->nullable();

            // количество
            $table->integer('quantity')->nullable();
            $table->integer('quantity_full')->nullable();

            // флаги
            $table->boolean('is_supply')->nullable();
            $table->boolean('is_realization')->nullable();

            // склады и логистика
            $table->string('warehouse_name')->nullable();
            $table->integer('in_way_to_client')->nullable();
            $table->integer('in_way_from_client')->nullable();

            // категории
            $table->string('subject')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();

            // доп
            $table->bigInteger('sc_code')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('discount')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
