<?php

namespace App\Models\Pricing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyRate extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'pricing.currency_rates';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider_id',
        'from_unit',
        'to_currency',
        'rate',
        'markup_percentage',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rate' => 'decimal:4',
        'markup_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить провайдера (nullable для общих курсов)
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    /**
     * Scope для получения только активных курсов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для получения валидных на текущий момент курсов
     */
    public function scopeValid($query)
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>', $now);
        });
    }

    /**
     * Scope для общих курсов (без привязки к провайдеру)
     */
    public function scopeGeneral($query)
    {
        return $query->whereNull('provider_id');
    }

    /**
     * Scope для курсов конкретного провайдера
     */
    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Рассчитать финальную сумму с наценкой
     */
    public function calculateWithMarkup(float $amount): float
    {
        $baseAmount = $amount * (float) $this->rate;
        $markup = $baseAmount * ((float) $this->markup_percentage / 100);

        return $baseAmount + $markup;
    }

    /**
     * Получить коэффициент конвертации с учетом наценки
     */
    public function getConversionRate(): float
    {
        return (float) $this->rate * (1 + (float) $this->markup_percentage / 100);
    }

    /**
     * Проверить, активен ли курс в данный момент
     */
    public function isCurrentlyValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $this->valid_from > $now) {
            return false;
        }

        if ($this->valid_until && $this->valid_until <= $now) {
            return false;
        }

        return true;
    }
}
