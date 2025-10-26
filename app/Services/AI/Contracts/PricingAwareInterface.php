<?php

namespace App\Services\AI\Contracts;

interface PricingAwareInterface
{
    /**
     * Получить ID провайдера из таблицы pricing.ai_providers
     *
     * @return int
     */
    public function getProviderId(): int;

    /**
     * Получить тип сервиса (image, infographic, text)
     *
     * @return string
     */
    public function getServiceType(): string;

    /**
     * Получить параметры для подбора прайсинга
     * Например: ['resolution' => '2500x900', 'model' => 'flux']
     *
     * @param array $requestData Данные запроса на генерацию
     * @return array
     */
    public function getPricingParameters(array $requestData): array;

    /**
     * Рассчитать стоимость генерации в кредитах
     *
     * @param array $requestData Данные запроса на генерацию
     * @return int
     */
    public function getCost(array $requestData = []): int;
}
