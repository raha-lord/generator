<?php

namespace App\Services\Pricing;

use App\Exceptions\PricingNotFoundException;
use App\Exceptions\ProviderInactiveException;
use App\Models\Pricing\AiProvider;
use App\Models\Pricing\CurrencyRate;
use App\Models\Pricing\ProviderPricing;
use Illuminate\Support\Facades\Log;

class PricingCalculator
{
    /**
     * Рассчитать стоимость генерации в кредитах
     *
     * @param string $serviceType Тип сервиса (image, infographic, text)
     * @param int $providerId ID провайдера из pricing.ai_providers
     * @param array $parameters Параметры для подбора прайсинга
     * @return int Стоимость в кредитах
     *
     * @throws PricingNotFoundException
     * @throws ProviderInactiveException
     */
    public function calculate(string $serviceType, int $providerId, array $parameters = []): int
    {
        // 1. Проверяем активность провайдера
        $provider = AiProvider::find($providerId);

        if (!$provider || !$provider->is_active) {
            $this->logMissingPricing($serviceType, $providerId, $parameters, 'Provider inactive');
            throw new ProviderInactiveException("Provider {$providerId} is inactive or not found");
        }

        // 2. Ищем точное совпадение прайсинга
        $pricing = $this->findExactMatch($serviceType, $providerId, $parameters);

        if ($pricing) {
            return $this->calculateFromPricing($pricing, $provider);
        }

        // 3. Пытаемся найти частичное совпадение (если включено)
        if (config('pricing.enable_partial_matching', true)) {
            $pricing = $this->findPartialMatch($serviceType, $providerId, $parameters);

            if ($pricing) {
                Log::info('Using partial pricing match', [
                    'service_type' => $serviceType,
                    'provider_id' => $providerId,
                    'pricing_id' => $pricing->id,
                    'parameters' => $parameters,
                ]);

                return $this->calculateFromPricing($pricing, $provider);
            }
        }

        // 4. Ищем дефолтную цену для сервиса (если включено)
        if (config('pricing.enable_default_fallback', true)) {
            $pricing = $this->findDefaultPricing($serviceType, $providerId);

            if ($pricing) {
                Log::warning('Using default pricing fallback', [
                    'service_type' => $serviceType,
                    'provider_id' => $providerId,
                    'pricing_id' => $pricing->id,
                ]);

                return $this->calculateFromPricing($pricing, $provider);
            }
        }

        // 5. Нет прайсинга - выбираем стратегию
        return $this->handleMissingPricing($serviceType, $providerId, $parameters);
    }

    /**
     * Найти точное совпадение прайсинга
     */
    protected function findExactMatch(string $serviceType, int $providerId, array $parameters): ?ProviderPricing
    {
        return ProviderPricing::where('service_type', $serviceType)
            ->where('provider_id', $providerId)
            ->where('is_active', true)
            ->get()
            ->first(function (ProviderPricing $pricing) use ($parameters) {
                return $pricing->matchesParameters($parameters);
            });
    }

    /**
     * Найти частичное совпадение прайсинга
     */
    protected function findPartialMatch(string $serviceType, int $providerId, array $parameters): ?ProviderPricing
    {
        $pricings = ProviderPricing::where('service_type', $serviceType)
            ->where('provider_id', $providerId)
            ->where('is_active', true)
            ->whereNotNull('conditions')
            ->get();

        // Ищем прайсинг с максимальным количеством совпадающих параметров
        $bestMatch = null;
        $maxMatches = 0;

        foreach ($pricings as $pricing) {
            $matches = 0;
            $conditions = $pricing->conditions ?? [];

            foreach ($conditions as $key => $value) {
                if (isset($parameters[$key]) && $parameters[$key] == $value) {
                    $matches++;
                }
            }

            if ($matches > $maxMatches && $matches > 0) {
                $maxMatches = $matches;
                $bestMatch = $pricing;
            }
        }

        return $bestMatch;
    }

    /**
     * Найти дефолтную цену для сервиса
     */
    protected function findDefaultPricing(string $serviceType, int $providerId): ?ProviderPricing
    {
        return ProviderPricing::where('service_type', $serviceType)
            ->where('provider_id', $providerId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Рассчитать финальную цену в кредитах из прайсинга
     */
    protected function calculateFromPricing(ProviderPricing $pricing, AiProvider $provider): int
    {
        // 1. Получаем стоимость в токенах API
        $apiTokens = $pricing->getTokenCost();

        // 2. Получаем курс конвертации провайдера
        $currencyRate = CurrencyRate::where('provider_id', $provider->id)
            ->where('is_active', true)
            ->valid()
            ->first();

        // Если нет курса для провайдера, используем общий курс
        if (!$currencyRate) {
            $currencyRate = CurrencyRate::whereNull('provider_id')
                ->where('is_active', true)
                ->where('to_currency', config('pricing.default_currency', 'RUB'))
                ->valid()
                ->first();
        }

        if (!$currencyRate) {
            // Если совсем нет курсов, используем токены напрямую как кредиты
            Log::warning('No currency rate found, using tokens directly as credits', [
                'provider_id' => $provider->id,
                'api_tokens' => $apiTokens,
            ]);

            return (int) ceil($apiTokens);
        }

        // 3. Конвертируем токены в валюту с учетом наценки
        $amountWithMarkup = $currencyRate->calculateWithMarkup($apiTokens);

        // 4. Конвертируем в кредиты (1 кредит = 1 RUB по умолчанию)
        $creditRate = config('pricing.credit_to_currency_rate', 1.0);
        $credits = $amountWithMarkup / $creditRate;

        // 5. Округляем вверх до целого
        $finalCredits = (int) ceil($credits);

        // Логируем расчёт
        if (config('pricing.log_pricing_calculations', true)) {
            Log::info('Pricing calculated', [
                'pricing_id' => $pricing->id,
                'pricing_key' => $pricing->pricing_key,
                'api_tokens' => $apiTokens,
                'currency_rate' => $currencyRate->rate,
                'markup_percentage' => $currencyRate->markup_percentage,
                'amount_with_markup' => $amountWithMarkup,
                'final_credits' => $finalCredits,
            ]);
        }

        return $finalCredits;
    }

    /**
     * Обработать отсутствие прайсинга
     */
    protected function handleMissingPricing(string $serviceType, int $providerId, array $parameters): int
    {
        $this->logMissingPricing($serviceType, $providerId, $parameters);

        $strategy = config('pricing.missing_pricing_strategy', 'fallback');

        switch ($strategy) {
            case 'fallback':
                return config("pricing.fallback.{$serviceType}", 10);

            case 'disable_service':
                throw new PricingNotFoundException(
                    "Service {$serviceType} is temporarily unavailable (no pricing configuration)"
                );

            case 'exception':
            default:
                throw new PricingNotFoundException(
                    "No pricing found for service={$serviceType}, provider={$providerId}"
                );
        }
    }

    /**
     * Логировать отсутствие прайсинга
     */
    protected function logMissingPricing(string $serviceType, int $providerId, array $parameters, string $reason = 'No matching pricing'): void
    {
        if (!config('pricing.log_missing_pricing', true)) {
            return;
        }

        Log::critical('Pricing configuration missing', [
            'reason' => $reason,
            'service_type' => $serviceType,
            'provider_id' => $providerId,
            'parameters' => $parameters,
            'action_required' => 'Add pricing configuration in admin panel',
        ]);
    }
}
