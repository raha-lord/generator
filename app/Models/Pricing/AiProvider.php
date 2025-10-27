<?php

namespace App\Models\Pricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends Model
{
    /**
     * Константы для ID провайдеров
     */
    const POLLINATIONS = 1;
    const GEMINI = 2;
    const OPENAI = 3;

    /**
     * The table associated with the model.
     */
    protected $table = 'ai_providers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'token_unit',
        'api_base_url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить все прайсинги провайдера
     */
    public function providerPricing(): HasMany
    {
        return $this->hasMany(ProviderPricing::class, 'provider_id');
    }

    /**
     * Получить курсы конвертации провайдера
     */
    public function currencyRates(): HasMany
    {
        return $this->hasMany(CurrencyRate::class, 'provider_id');
    }

    /**
     * Проверить, есть ли активный прайсинг для сервиса
     */
    public function hasPricingFor(string $serviceType): bool
    {
        return $this->providerPricing()
            ->where('service_type', $serviceType)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Проверить, может ли провайдер генерировать контент
     */
    public function canGenerate(string $serviceType): bool
    {
        return $this->is_active && $this->hasPricingFor($serviceType);
    }

    /**
     * Scope для получения только активных провайдеров
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить активный курс конвертации
     */
    public function getActiveCurrencyRate(string $toCurrency = 'RUB')
    {
        return $this->currencyRates()
            ->where('is_active', true)
            ->where('to_currency', $toCurrency)
            ->whereNull('valid_until')
            ->orWhere('valid_until', '>', now())
            ->first();
    }
}
