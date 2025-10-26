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
        DB::statement('CREATE TABLE chat.services (
            id BIGSERIAL PRIMARY KEY,
            code VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            type VARCHAR(50) NOT NULL DEFAULT \'simple\',
            icon VARCHAR(255),
            color VARCHAR(50),
            is_active BOOLEAN NOT NULL DEFAULT true,
            config JSONB,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE,

            CONSTRAINT chat_services_type_check CHECK (type IN (\'simple\', \'multi_step\'))
        )');

        // Индексы
        DB::statement('CREATE INDEX chat_services_code_index ON chat.services(code)');
        DB::statement('CREATE INDEX chat_services_is_active_index ON chat.services(is_active)');

        // Комментарии
        DB::statement("COMMENT ON TABLE chat.services IS 'Типы сервисов (text_chat, image_gen, song_creation и тд)'");
        DB::statement("COMMENT ON COLUMN chat.services.type IS 'simple - одношаговый, multi_step - многошаговый'");
        DB::statement("COMMENT ON COLUMN chat.services.config IS 'JSON конфигурация сервиса (дефолтные параметры)'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS chat.services CASCADE');
    }
};
