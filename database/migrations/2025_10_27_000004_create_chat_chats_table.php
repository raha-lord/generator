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
        DB::statement('CREATE TABLE chat.chats (
            id BIGSERIAL PRIMARY KEY,
            uuid UUID NOT NULL UNIQUE DEFAULT gen_random_uuid(),
            user_id BIGINT NOT NULL,
            service_id BIGINT NOT NULL,
            title VARCHAR(255),
            status VARCHAR(50) NOT NULL DEFAULT \'active\',
            current_step_order INTEGER DEFAULT 1,
            metadata JSONB,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE,

            CONSTRAINT chat_chats_user_id_foreign
                FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE,
            CONSTRAINT chat_chats_service_id_foreign
                FOREIGN KEY (service_id) REFERENCES chat.services(id) ON DELETE RESTRICT,
            CONSTRAINT chat_chats_status_check
                CHECK (status IN (\'active\', \'completed\', \'archived\', \'failed\'))
        )');

        // Индексы
        DB::statement('CREATE INDEX chat_chats_uuid_index ON chat.chats(uuid)');
        DB::statement('CREATE INDEX chat_chats_user_id_index ON chat.chats(user_id)');
        DB::statement('CREATE INDEX chat_chats_service_id_index ON chat.chats(service_id)');
        DB::statement('CREATE INDEX chat_chats_status_index ON chat.chats(status)');
        DB::statement('CREATE INDEX chat_chats_created_at_index ON chat.chats(created_at)');

        // Комментарии
        DB::statement("COMMENT ON TABLE chat.chats IS 'Чаты пользователей (сессии работы с сервисом)'");
        DB::statement("COMMENT ON COLUMN chat.chats.uuid IS 'UUID для публичного доступа'");
        DB::statement("COMMENT ON COLUMN chat.chats.title IS 'Название чата (автогенерируется или задаётся пользователем)'");
        DB::statement("COMMENT ON COLUMN chat.chats.status IS 'Статус чата: active, completed, archived, failed'");
        DB::statement("COMMENT ON COLUMN chat.chats.current_step_order IS 'Номер текущего шага workflow'");
        DB::statement("COMMENT ON COLUMN chat.chats.metadata IS 'JSON данные чата (настройки, параметры)'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS chat.chats CASCADE');
    }
};
