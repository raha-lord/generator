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
        DB::statement('CREATE TABLE chat.artifacts (
            id BIGSERIAL PRIMARY KEY,
            uuid UUID NOT NULL UNIQUE DEFAULT gen_random_uuid(),
            chat_id BIGINT NOT NULL,
            message_id BIGINT NOT NULL,
            type VARCHAR(50) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT NOT NULL DEFAULT 0,
            mime_type VARCHAR(100),
            metadata JSONB,
            is_public BOOLEAN NOT NULL DEFAULT false,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE,

            CONSTRAINT chat_artifacts_chat_id_foreign
                FOREIGN KEY (chat_id) REFERENCES chat.chats(id) ON DELETE CASCADE,
            CONSTRAINT chat_artifacts_message_id_foreign
                FOREIGN KEY (message_id) REFERENCES chat.messages(id) ON DELETE CASCADE,
            CONSTRAINT chat_artifacts_type_check
                CHECK (type IN (\'image\', \'audio\', \'video\', \'document\'))
        )');

        // Индексы
        DB::statement('CREATE INDEX chat_artifacts_uuid_index ON chat.artifacts(uuid)');
        DB::statement('CREATE INDEX chat_artifacts_chat_id_index ON chat.artifacts(chat_id)');
        DB::statement('CREATE INDEX chat_artifacts_message_id_index ON chat.artifacts(message_id)');
        DB::statement('CREATE INDEX chat_artifacts_type_index ON chat.artifacts(type)');
        DB::statement('CREATE INDEX chat_artifacts_is_public_index ON chat.artifacts(is_public)');

        // Комментарии
        DB::statement("COMMENT ON TABLE chat.artifacts IS 'Файлы и артефакты, созданные в чатах (изображения, аудио, видео)'");
        DB::statement("COMMENT ON COLUMN chat.artifacts.uuid IS 'UUID для публичного доступа к файлу'");
        DB::statement("COMMENT ON COLUMN chat.artifacts.type IS 'Тип артефакта: image, audio, video, document'");
        DB::statement("COMMENT ON COLUMN chat.artifacts.metadata IS 'JSON метаданные (разрешение, модель, seed и тд)'");
        DB::statement("COMMENT ON COLUMN chat.artifacts.is_public IS 'Доступен ли файл публично'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS chat.artifacts CASCADE');
    }
};
