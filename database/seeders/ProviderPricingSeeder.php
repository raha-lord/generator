<?php

namespace Database\Seeders;

use App\Models\Pricing\AiProvider;
use App\Models\Pricing\ProviderPricing;
use Illuminate\Database\Seeder;

class ProviderPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pricings = [
            // Pollinations - Image generation
            [
                'provider_id' => AiProvider::POLLINATIONS,
                'service_type' => 'image',
                'pricing_key' => 'pollinations_image_512x512',
                'display_name' => 'Изображение 512×512 (Стандарт)',
                'token_cost' => 5,
                'conditions' => ['resolution' => '512x512'],
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'provider_id' => AiProvider::POLLINATIONS,
                'service_type' => 'image',
                'pricing_key' => 'pollinations_image_1024x1024',
                'display_name' => 'Изображение 1024×1024',
                'token_cost' => 10,
                'conditions' => ['resolution' => '1024x1024'],
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'provider_id' => AiProvider::POLLINATIONS,
                'service_type' => 'image',
                'pricing_key' => 'pollinations_image_2500x900',
                'display_name' => 'Изображение 2500×900 (HD)',
                'token_cost' => 26,
                'conditions' => ['resolution' => '2500x900'],
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'provider_id' => AiProvider::POLLINATIONS,
                'service_type' => 'image',
                'pricing_key' => 'pollinations_image_560x560',
                'display_name' => 'Изображение 560×560 (Эконом)',
                'token_cost' => 5,
                'conditions' => ['resolution' => '560x560'],
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],

            // Gemini - Image generation (used for infographics)
            [
                'provider_id' => AiProvider::GEMINI,
                'service_type' => 'image',
                'pricing_key' => 'gemini_image_1024x1024',
                'display_name' => 'Изображение 1024×1024 (Gemini)',
                'token_cost' => 10,
                'conditions' => ['resolution' => '1024x1024'],
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'provider_id' => AiProvider::GEMINI,
                'service_type' => 'image',
                'pricing_key' => 'gemini_image_2048x2048',
                'display_name' => 'Изображение 2048×2048 (Gemini HD)',
                'token_cost' => 20,
                'conditions' => ['resolution' => '2048x2048'],
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($pricings as $pricing) {
            ProviderPricing::updateOrCreate(
                ['pricing_key' => $pricing['pricing_key']],
                $pricing
            );
        }

        $this->command->info('Provider Pricing seeded successfully!');
        $this->command->info('Total pricing configurations: ' . count($pricings));
    }
}
