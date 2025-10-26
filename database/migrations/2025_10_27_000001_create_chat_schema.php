<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Создаем схему chat
        DB::statement('CREATE SCHEMA IF NOT EXISTS chat');

        // Комментарий для документации
        DB::statement("COMMENT ON SCHEMA chat IS 'Схема для таблиц чатов и сообщений'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем схему chat
        DB::statement('DROP SCHEMA IF EXISTS chat CASCADE');
    }
};
