<?php

namespace App\Models\Pricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderPricing extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'provider_pricing';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider_id',
        'service_type',
        'pricing_key',
        'display_name',
        'token_cost',
        'conditions',
        'is_default',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'token_cost' => 'decimal:4',
        'conditions' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить провайдера
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    /**
     * Scope для получения только активных прайсингов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для получения дефолтных прайсингов
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope для поиска по типу сервиса
     */
    public function scopeForService($query, string $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    /**
     * Scope для поиска по провайдеру
     */
    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Проверить, соответствуют ли параметры условиям прайсинга
     */
    public function matchesParameters(array $parameters): bool
    {
        if (empty($this->conditions)) {
            return true; // Если нет условий, подходит для любых параметров
        }

        foreach ($this->conditions as $key => $value) {
            // Если параметр отсутствует
            if (!isset($parameters[$key])) {
                return false;
            }

            // Если значения не совпадают (поддержка массива значений)
            if (is_array($value)) {
                if (!in_array($parameters[$key], $value)) {
                    return false;
                }
            } else {
                if ($parameters[$key] != $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Получить стоимость в токенах
     */
    public function getTokenCost(): float
    {
        return (float) $this->token_cost;
    }
}
