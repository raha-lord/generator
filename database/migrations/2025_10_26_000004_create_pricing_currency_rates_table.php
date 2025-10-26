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
        // Создаем таблицу курсов конвертации
        DB::statement("
            CREATE TABLE pricing.currency_rates (
                id BIGSERIAL PRIMARY KEY,
                provider_id BIGINT,
                from_unit VARCHAR(20) NOT NULL,
                to_currency VARCHAR(10) NOT NULL,
                rate DECIMAL(10,4) NOT NULL,
                markup_percentage DECIMAL(5,2) DEFAULT 0 NOT NULL,
                is_active BOOLEAN DEFAULT true NOT NULL,
                valid_from TIMESTAMP(0) WITHOUT TIME ZONE,
                valid_until TIMESTAMP(0) WITHOUT TIME ZONE,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE,

                CONSTRAINT fk_currency_rates_provider
                    FOREIGN KEY (provider_id)
                    REFERENCES pricing.ai_providers (id)
                    ON DELETE CASCADE,

                CONSTRAINT check_rate_positive
                    CHECK (rate > 0),

                CONSTRAINT check_markup_non_negative
                    CHECK (markup_percentage >= 0)
            )
        ");

        // Индексы для быстрого поиска курсов
        DB::statement("
            CREATE INDEX currency_rates_lookup_index
            ON pricing.currency_rates (provider_id, is_active, valid_from, valid_until)
        ");

        DB::statement("
            CREATE INDEX currency_rates_general_index
            ON pricing.currency_rates (from_unit, to_currency, is_active)
            WHERE provider_id IS NULL
        ");

        // Комментарии
        DB::statement("
            COMMENT ON TABLE pricing.currency_rates IS 'Курсы конвертации токенов API в валюту/кредиты'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.currency_rates.provider_id IS 'ID провайдера (NULL = общий курс для всех)'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.currency_rates.from_unit IS 'Единица измерения источника: tokens, requests, credits'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.currency_rates.to_currency IS 'Целевая валюта: RUB, USD, credits'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.currency_rates.rate IS 'Курс: 1 from_unit = X to_currency'
        ");
        DB::statement("
            COMMENT ON COLUMN pricing.currency_rates.markup_percentage IS 'Наценка в процентах (20.00 = 20%)'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS pricing.currency_rates CASCADE');
    }
};
