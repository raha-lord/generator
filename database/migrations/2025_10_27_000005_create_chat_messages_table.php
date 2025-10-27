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
        DB::statement('CREATE TABLE chat.messages (
            id BIGSERIAL PRIMARY KEY,
            chat_id BIGINT NOT NULL,
            workflow_step_id BIGINT,
            type VARCHAR(50) NOT NULL,
            role VARCHAR(50) NOT NULL,
            content TEXT NOT NULL,
            metadata JSONB,
            credits_spent DECIMAL(10, 2) DEFAULT 0,
            status VARCHAR(50) NOT NULL DEFAULT \'completed\',
            created_at TIMESTAMP(0) WITHOUT TIME ZONE,

            CONSTRAINT chat_messages_chat_id_foreign
                FOREIGN KEY (chat_id) REFERENCES chat.chats(id) ON DELETE CASCADE,
            CONSTRAINT chat_messages_workflow_step_id_foreign
                FOREIGN KEY (workflow_step_id) REFERENCES chat.workflow_steps(id) ON DELETE SET NULL,
            CONSTRAINT chat_messages_type_check
                CHECK (type IN (\'user_input\', \'assistant_response\', \'system\', \'step_result\', \'step_confirmation\')),
            CONSTRAINT chat_messages_role_check
                CHECK (role IN (\'user\', \'assistant\', \'system\')),
            CONSTRAINT chat_messages_status_check
                CHECK (status IN (\'pending\', \'processing\', \'completed\', \'failed\'))
        )');

        // Индексы
        DB::statement('CREATE INDEX chat_messages_chat_id_index ON chat.messages(chat_id)');
        DB::statement('CREATE INDEX chat_messages_workflow_step_id_index ON chat.messages(workflow_step_id)');
        DB::statement('CREATE INDEX chat_messages_type_index ON chat.messages(type)');
        DB::statement('CREATE INDEX chat_messages_role_index ON chat.messages(role)');
        DB::statement('CREATE INDEX chat_messages_created_at_index ON chat.messages(created_at)');

        // Комментарии
        DB::statement("COMMENT ON TABLE chat.messages IS 'Сообщения в чатах (пользователь + AI + система)'");
        DB::statement("COMMENT ON COLUMN chat.messages.workflow_step_id IS 'ID шага workflow (если сообщение - результат шага)'");
        DB::statement("COMMENT ON COLUMN chat.messages.type IS 'Тип сообщения: user_input, assistant_response, system, step_result, step_confirmation'");
        DB::statement("COMMENT ON COLUMN chat.messages.role IS 'Роль для AI API: user, assistant, system'");
        DB::statement("COMMENT ON COLUMN chat.messages.content IS 'Содержимое сообщения (текст или JSON)'");
        DB::statement("COMMENT ON COLUMN chat.messages.metadata IS 'JSON метаданные (модель, токены, параметры генерации)'");
        DB::statement("COMMENT ON COLUMN chat.messages.credits_spent IS 'Потрачено кредитов на это сообщение'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS chat.messages CASCADE');
    }
};
