<?php

namespace App\Services\AI\Concerns;

use App\Exceptions\PricingNotFoundException;
use App\Services\Pricing\PricingCalculator;
use Illuminate\Support\Facades\Log;

trait HasPricing
{
    /**
     * Калькулятор прайсинга
     */
    protected ?PricingCalculator $pricingCalculator = null;

    /**
     * Получить инстанс калькулятора прайсинга
     */
    protected function getPricingCalculator(): PricingCalculator
    {
        if ($this->pricingCalculator === null) {
            $this->pricingCalculator = app(PricingCalculator::class);
        }

        return $this->pricingCalculator;
    }

    /**
     * Рассчитать стоимость генерации в кредитах
     *
     * @param array $requestData Данные запроса на генерацию
     * @return int Стоимость в кредитах
     */
    public function getCost(array $requestData = []): int
    {
        try {
            return $this->getPricingCalculator()->calculate(
                serviceType: $this->getServiceType(),
                providerId: $this->getProviderId(),
                parameters: $this->getPricingParameters($requestData)
            );
        } catch (PricingNotFoundException $e) {
            // Обработка отсутствия прайсинга
            return $this->handleMissingPricing($e);
        }
    }

    /**
     * Обработать отсутствие прайсинга
     *
     * Этот метод можно переопределить в дочернем классе
     * для кастомной обработки отсутствия цен
     *
     * @param PricingNotFoundException $exception
     * @return int
     * @throws PricingNotFoundException
     */
    protected function handleMissingPricing(PricingNotFoundException $exception): int
    {
        // Стратегия по умолчанию: пробросить исключение дальше
        // или использовать fallback из конфига

        $strategy = config('pricing.missing_pricing_strategy', 'fallback');

        if ($strategy === 'fallback') {
            $fallbackCost = config("pricing.fallback.{$this->getServiceType()}", 10);

            Log::warning('Using fallback pricing for missing configuration', [
                'service_type' => $this->getServiceType(),
                'provider_id' => $this->getProviderId(),
                'fallback_cost' => $fallbackCost,
            ]);

            return $fallbackCost;
        }

        // Для остальных стратегий бросаем исключение
        throw $exception;
    }

    /**
     * Абстрактные методы, которые должны быть реализованы в классе
     *
     * Эти методы определяются в интерфейсе PricingAwareInterface
     */
    abstract protected function getProviderId(): int;
    abstract protected function getServiceType(): string;
    abstract protected function getPricingParameters(array $requestData): array;
}
