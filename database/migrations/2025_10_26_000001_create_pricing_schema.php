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
        // Создаем схему pricing для изоляции таблиц прайсинга
        DB::statement('CREATE SCHEMA IF NOT EXISTS pricing');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем схему со всеми объектами (CASCADE удалит все таблицы внутри)
        DB::statement('DROP SCHEMA IF EXISTS pricing CASCADE');
    }
};
