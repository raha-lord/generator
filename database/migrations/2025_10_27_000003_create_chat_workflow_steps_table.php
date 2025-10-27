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
        DB::statement('CREATE TABLE chat.workflow_steps (
            id BIGSERIAL PRIMARY KEY,
            service_id BIGINT NOT NULL,
            "order" INTEGER NOT NULL,
            code VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            model_type VARCHAR(50) NOT NULL,
            provider_id BIGINT,
            requires_confirmation BOOLEAN NOT NULL DEFAULT true,
            prompt_template TEXT,
            config JSONB,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE,

            CONSTRAINT chat_workflow_steps_service_id_foreign
                FOREIGN KEY (service_id) REFERENCES chat.services(id) ON DELETE CASCADE,
            CONSTRAINT chat_workflow_steps_provider_id_foreign
                FOREIGN KEY (provider_id) REFERENCES pricing.ai_providers(id) ON DELETE SET NULL,
            CONSTRAINT chat_workflow_steps_model_type_check
                CHECK (model_type IN (\'text\', \'image\', \'audio\', \'video\')),
            CONSTRAINT chat_workflow_steps_service_order_unique
                UNIQUE (service_id, "order")
        )');

        // Индексы
        DB::statement('CREATE INDEX chat_workflow_steps_service_id_index ON chat.workflow_steps(service_id)');
        DB::statement('CREATE INDEX chat_workflow_steps_model_type_index ON chat.workflow_steps(model_type)');
        DB::statement('CREATE INDEX chat_workflow_steps_order_index ON chat.workflow_steps("order")');

        // Комментарии
        DB::statement("COMMENT ON TABLE chat.workflow_steps IS 'Шаги workflow для каждого сервиса'");
        DB::statement("COMMENT ON COLUMN chat.workflow_steps.\"order\" IS 'Порядок выполнения шага (1, 2, 3...)'");
        DB::statement("COMMENT ON COLUMN chat.workflow_steps.model_type IS 'Тип модели: text, image, audio, video'");
        DB::statement("COMMENT ON COLUMN chat.workflow_steps.provider_id IS 'Провайдер по умолчанию (nullable - выбирается динамически)'");
        DB::statement("COMMENT ON COLUMN chat.workflow_steps.requires_confirmation IS 'Требуется ли подтверждение пользователя перед выполнением'");
        DB::statement("COMMENT ON COLUMN chat.workflow_steps.prompt_template IS 'Шаблон промпта для AI (может содержать переменные)'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS chat.workflow_steps CASCADE');
    }
};
