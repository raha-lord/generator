<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Сначала создаём пользователей и балансы
            UserSeeder::class,

            // Затем настраиваем систему прайсинга
            AiProviderSeeder::class,
            ProviderPricingSeeder::class,
            CurrencyRateSeeder::class,
        ]);
    }
}
