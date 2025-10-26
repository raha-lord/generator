<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fallback Prices
    |--------------------------------------------------------------------------
    |
    | Эти цены используются как fallback, если не найдена конфигурация
    | прайсинга в БД. Значения указаны в кредитах.
    |
    */
    'fallback' => [
        'image' => env('PRICING_FALLBACK_IMAGE', 5),
        'infographic' => env('PRICING_FALLBACK_INFOGRAPHIC', 10),
        'text' => env('PRICING_FALLBACK_TEXT', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing Strategy
    |--------------------------------------------------------------------------
    |
    | Определяет поведение системы при отсутствии прайсинга:
    | - 'exception': Бросить исключение (по умолчанию)
    | - 'fallback': Использовать fallback цены
    | - 'disable_service': Отключить сервис
    |
    */
    'missing_pricing_strategy' => env('PRICING_MISSING_STRATEGY', 'fallback'),

    /*
    |--------------------------------------------------------------------------
    | Matching Options
    |--------------------------------------------------------------------------
    |
    | Настройки для поиска подходящего прайсинга
    |
    */
    'enable_partial_matching' => env('PRICING_PARTIAL_MATCHING', true),
    'enable_default_fallback' => env('PRICING_DEFAULT_FALLBACK', true),

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Настройки валюты для расчёта цен
    |
    */
    'default_currency' => env('PRICING_DEFAULT_CURRENCY', 'RUB'),
    'credit_to_currency_rate' => env('PRICING_CREDIT_RATE', 1.0), // 1 кредит = X валюты

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Логирование действий прайсинга
    |
    */
    'log_pricing_calculations' => env('PRICING_LOG_CALCULATIONS', true),
    'log_missing_pricing' => env('PRICING_LOG_MISSING', true),
];
