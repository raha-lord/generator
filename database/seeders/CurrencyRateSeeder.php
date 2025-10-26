<?php

namespace Database\Seeders;

use App\Models\Pricing\AiProvider;
use App\Models\Pricing\CurrencyRate;
use Illuminate\Database\Seeder;

class CurrencyRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [
            // Курс для Pollinations: 1 request = 1₽ + 20% наценка
            [
                'provider_id' => AiProvider::POLLINATIONS,
                'from_unit' => 'requests',
                'to_currency' => 'RUB',
                'rate' => 1.0,
                'markup_percentage' => 20.00, // 20% наценка
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => null, // Бессрочно
            ],

            // Курс для Gemini: 1 token = 0.01₽ + 30% наценка
            [
                'provider_id' => AiProvider::GEMINI,
                'from_unit' => 'tokens',
                'to_currency' => 'RUB',
                'rate' => 0.01,
                'markup_percentage' => 30.00, // 30% наценка
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => null,
            ],

            // Общий курс кредитов (не привязан к провайдеру)
            [
                'provider_id' => null,
                'from_unit' => 'credits',
                'to_currency' => 'RUB',
                'rate' => 1.0, // 1 кредит = 1 рубль
                'markup_percentage' => 0, // Без наценки
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => null,
            ],

            // Курс для OpenAI (для будущего использования)
            [
                'provider_id' => AiProvider::OPENAI,
                'from_unit' => 'tokens',
                'to_currency' => 'RUB',
                'rate' => 0.05,
                'markup_percentage' => 25.00,
                'is_active' => false, // Пока неактивен
                'valid_from' => now(),
                'valid_until' => null,
            ],
        ];

        foreach ($rates as $rate) {
            // Используем комбинацию полей для уникальности
            CurrencyRate::updateOrCreate(
                [
                    'provider_id' => $rate['provider_id'],
                    'from_unit' => $rate['from_unit'],
                    'to_currency' => $rate['to_currency'],
                ],
                $rate
            );
        }

        $this->command->info('Currency Rates seeded successfully!');
        $this->command->info('Total rates: ' . count($rates));

        // Вывод примера расчёта
        $this->command->info("\n📊 Примеры расчёта стоимости:");
        $this->command->info("Pollinations (512×512): 5 requests × 1₽ × 1.2 = 6₽ = 6 кредитов");
        $this->command->info("Pollinations (2500×900): 26 requests × 1₽ × 1.2 = 31.2₽ = 32 кредита");
        $this->command->info("Gemini (1024×1024): 10 tokens × 0.01₽ × 1.3 = 0.13₽ = 1 кредит");
    }
}
