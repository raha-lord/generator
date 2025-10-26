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
        // Удаляем старые таблицы из схемы generations
        DB::statement('DROP TABLE IF EXISTS generations.infographics CASCADE');
        DB::statement('DROP TABLE IF EXISTS generations.images CASCADE');
        DB::statement('DROP TABLE IF EXISTS generations.generations CASCADE');

        // Удаляем схему generations (она больше не нужна)
        DB::statement('DROP SCHEMA IF EXISTS generations CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Примечание: откат этой миграции не восстанавливает данные
        // Это необратимая операция, данные будут потеряны

        DB::statement('CREATE SCHEMA IF NOT EXISTS generations');

        // Восстанавливаем структуру таблиц (без данных)
        DB::statement('CREATE TABLE generations.generations (
            id BIGSERIAL PRIMARY KEY,
            uuid UUID NOT NULL UNIQUE,
            user_id BIGINT NOT NULL,
            generatable_type VARCHAR(255) NOT NULL,
            generatable_id BIGINT NOT NULL,
            status VARCHAR(50) NOT NULL,
            is_public BOOLEAN DEFAULT false,
            created_at TIMESTAMP(0),
            updated_at TIMESTAMP(0),
            FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE
        )');

        DB::statement('CREATE TABLE generations.images (
            id BIGSERIAL PRIMARY KEY,
            uuid UUID NOT NULL UNIQUE,
            user_id BIGINT NOT NULL,
            prompt TEXT NOT NULL,
            model VARCHAR(255),
            file_path VARCHAR(500),
            file_size BIGINT,
            width INTEGER,
            height INTEGER,
            seed BIGINT,
            enhance BOOLEAN,
            metadata JSONB,
            created_at TIMESTAMP(0),
            updated_at TIMESTAMP(0),
            FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE
        )');

        DB::statement('CREATE TABLE generations.infographics (
            id BIGSERIAL PRIMARY KEY,
            uuid UUID NOT NULL UNIQUE,
            user_id BIGINT NOT NULL,
            prompt TEXT NOT NULL,
            style VARCHAR(100),
            slides JSONB,
            provider VARCHAR(100),
            metadata JSONB,
            created_at TIMESTAMP(0),
            updated_at TIMESTAMP(0),
            FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE
        )');
    }
};
