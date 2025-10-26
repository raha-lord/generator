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
        // Создаем схемы balance и generations
        DB::statement('CREATE SCHEMA IF NOT EXISTS balance');
        DB::statement('CREATE SCHEMA IF NOT EXISTS generations');

        // Переносим таблицы балансов в схему balance
        if ($this->tableExists('balances')) {
            DB::statement('ALTER TABLE balances SET SCHEMA balance');
        }
        if ($this->tableExists('balance_transactions')) {
            DB::statement('ALTER TABLE balance_transactions SET SCHEMA balance');
        }

        // Переносим таблицы генераций в схему generations
        if ($this->tableExists('generations')) {
            DB::statement('ALTER TABLE generations SET SCHEMA generations');
        }
        if ($this->tableExists('images')) {
            DB::statement('ALTER TABLE images SET SCHEMA generations');
        }
        if ($this->tableExists('infographics')) {
            DB::statement('ALTER TABLE infographics SET SCHEMA generations');
        }

        // Комментарии для документации
        DB::statement("COMMENT ON SCHEMA balance IS 'Схема для таблиц управления балансом пользователей'");
        DB::statement("COMMENT ON SCHEMA generations IS 'Схема для таблиц генераций контента'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем таблицы обратно в public схему
        if ($this->tableExists('balance.balances')) {
            DB::statement('ALTER TABLE balance.balances SET SCHEMA public');
        }
        if ($this->tableExists('balance.balance_transactions')) {
            DB::statement('ALTER TABLE balance.balance_transactions SET SCHEMA public');
        }
        if ($this->tableExists('generations.generations')) {
            DB::statement('ALTER TABLE generations.generations SET SCHEMA public');
        }
        if ($this->tableExists('generations.images')) {
            DB::statement('ALTER TABLE generations.images SET SCHEMA public');
        }
        if ($this->tableExists('generations.infographics')) {
            DB::statement('ALTER TABLE generations.infographics SET SCHEMA public');
        }

        // Удаляем схемы
        DB::statement('DROP SCHEMA IF EXISTS balance CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS generations CASCADE');
    }

    /**
     * Check if table exists in any schema
     */
    private function tableExists(string $tableName): bool
    {
        $parts = explode('.', $tableName);

        if (count($parts) === 2) {
            // Проверка с указанием схемы
            [$schema, $table] = $parts;
            $result = DB::select("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables
                    WHERE table_schema = ?
                    AND table_name = ?
                )
            ", [$schema, $table]);
        } else {
            // Проверка в public схеме
            $result = DB::select("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables
                    WHERE table_schema = 'public'
                    AND table_name = ?
                )
            ", [$tableName]);
        }

        return $result[0]->exists ?? false;
    }
};
