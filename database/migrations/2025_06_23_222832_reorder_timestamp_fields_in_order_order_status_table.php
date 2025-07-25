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
        Schema::table('order_order_status', function (Blueprint $table) {
            // Удаляем существующие временные поля
            $table->dropTimestamps();
        });

        Schema::table('order_order_status', function (Blueprint $table) {
            // Добавляем временные поля в конец таблицы
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // В случае отката оставляем поля как есть
        // Порядок полей в rollback не критичен
    }
};
