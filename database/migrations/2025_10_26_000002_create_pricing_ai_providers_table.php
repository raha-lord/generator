<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Создаем таблицу в схеме pricing
        DB::statement("
            CREATE TABLE pricing.ai_providers (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(50) UNIQUE NOT NULL,
                display_name VARCHAR(100) NOT NULL,
                token_unit VARCHAR(20) NOT NULL,
                api_base_url VARCHAR(255),
                is_active BOOLEAN DEFAULT true NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE
            )
        ");

        // Создаем индекс для быстрого поиска активных провайдеров
        DB::statement("
            CREATE INDEX ai_providers_is_active_index
            ON pricing.ai_providers (is_active)
        ");

        // Комментарии для документации
        DB::statement("
            COMMENT ON TABLE pricing.ai_providers IS 'AI провайдеры (Pollinations, Gemini, OpenAI и т.д.)'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.ai_providers.name IS 'Уникальное имя провайдера (pollinations, gemini, openai)'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.ai_providers.token_unit IS 'Единица измерения токенов (tokens, credits, requests)'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS pricing.ai_providers CASCADE');
    }
};
