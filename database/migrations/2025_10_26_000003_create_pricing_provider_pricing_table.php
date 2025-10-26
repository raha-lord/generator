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
        // Создаем таблицу прайсинга провайдеров
        DB::statement("
            CREATE TABLE pricing.provider_pricing (
                id BIGSERIAL PRIMARY KEY,
                provider_id BIGINT NOT NULL,
                service_type VARCHAR(50) NOT NULL,
                pricing_key VARCHAR(100) UNIQUE NOT NULL,
                display_name VARCHAR(200) NOT NULL,
                token_cost DECIMAL(10,4) NOT NULL,
                conditions JSONB,
                is_default BOOLEAN DEFAULT false NOT NULL,
                is_active BOOLEAN DEFAULT true NOT NULL,
                sort_order INTEGER DEFAULT 0 NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE,

                CONSTRAINT fk_provider_pricing_provider
                    FOREIGN KEY (provider_id)
                    REFERENCES pricing.ai_providers (id)
                    ON DELETE CASCADE
            )
        ");

        // Индексы для быстрого поиска
        DB::statement("
            CREATE INDEX provider_pricing_lookup_index
            ON pricing.provider_pricing (provider_id, service_type, is_active)
        ");

        DB::statement("
            CREATE INDEX provider_pricing_default_index
            ON pricing.provider_pricing (service_type, is_default, is_active)
        ");

        // GIN индекс для поиска по JSONB условиям
        DB::statement("
            CREATE INDEX provider_pricing_conditions_index
            ON pricing.provider_pricing USING GIN (conditions)
        ");

        // Комментарии
        DB::statement("
            COMMENT ON TABLE pricing.provider_pricing IS 'Прайсинг провайдеров: стоимость в токенах API для различных параметров'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.provider_pricing.service_type IS 'Тип сервиса: image, infographic, text'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.provider_pricing.token_cost IS 'Стоимость в токенах/единицах API'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.provider_pricing.conditions IS 'JSON с условиями применения: {\"resolution\": \"2500x900\", \"model\": \"flux\"}'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS pricing.provider_pricing CASCADE');
    }
};
