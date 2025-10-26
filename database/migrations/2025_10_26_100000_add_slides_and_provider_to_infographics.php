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
        // Add slides JSONB column to store multiple slide paths
        // Add provider_id to track which AI provider was used
        // Add slides_count for convenience
        DB::statement("
            ALTER TABLE generations.infographics
            ADD COLUMN slides JSONB DEFAULT '[]'::jsonb,
            ADD COLUMN provider_id INTEGER REFERENCES pricing.ai_providers(id) ON DELETE SET NULL,
            ADD COLUMN slides_count INTEGER DEFAULT 1 NOT NULL
        ");

        // Create index on slides JSONB column for better performance
        DB::statement("
            CREATE INDEX infographics_slides_idx ON generations.infographics USING GIN (slides)
        ");

        // Add comment for documentation
        DB::statement("
            COMMENT ON COLUMN generations.infographics.slides IS 'Array of slide paths for multi-slide infographics'
        ");
        DB::statement("
            COMMENT ON COLUMN generations.infographics.provider_id IS 'AI provider used for generation (Pollinations, Gemini, etc.)'
        ");
        DB::statement("
            COMMENT ON COLUMN generations.infographics.slides_count IS 'Number of slides in the infographic'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE generations.infographics
            DROP COLUMN IF EXISTS slides,
            DROP COLUMN IF EXISTS provider_id,
            DROP COLUMN IF EXISTS slides_count
        ");
    }
};
