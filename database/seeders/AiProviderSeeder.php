<?php

namespace Database\Seeders;

use App\Models\Pricing\AiProvider;
use Illuminate\Database\Seeder;

class AiProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'id' => AiProvider::POLLINATIONS,
                'name' => 'pollinations',
                'display_name' => 'Pollinations AI',
                'token_unit' => 'requests',
                'api_base_url' => 'https://image.pollinations.ai',
                'is_active' => true,
            ],
            [
                'id' => AiProvider::GEMINI,
                'name' => 'gemini',
                'display_name' => 'Google Gemini',
                'token_unit' => 'tokens',
                'api_base_url' => null,
                'is_active' => true,
            ],
            [
                'id' => AiProvider::OPENAI,
                'name' => 'openai',
                'display_name' => 'OpenAI',
                'token_unit' => 'tokens',
                'api_base_url' => 'https://api.openai.com/v1',
                'is_active' => false, // Пока не используется
            ],
        ];

        foreach ($providers as $provider) {
            AiProvider::updateOrCreate(
                ['id' => $provider['id']],
                $provider
            );
        }

        $this->command->info('AI Providers seeded successfully!');
    }
}
