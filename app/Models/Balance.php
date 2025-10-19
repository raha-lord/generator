<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balance extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'credits',
        'reserved_credits',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'reserved_credits' => 'integer',
        ];
    }

    /**
     * Get the user that owns the balance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get available credits (total - reserved).
     */
    public function getAvailableCreditsAttribute(): int
    {
        return $this->credits - $this->reserved_credits;
    }

    /**
     * Check if user has enough credits.
     */
    public function hasEnoughCredits(int $amount): bool
    {
        return $this->getAvailableCreditsAttribute() >= $amount;
    }

    /**
     * Reserve credits for a transaction.
     */
    public function reserveCredits(int $amount): bool
    {
        if (!$this->hasEnoughCredits($amount)) {
            return false;
        }

        $this->reserved_credits += $amount;
        return $this->save();
    }

    /**
     * Release reserved credits.
     */
    public function releaseCredits(int $amount): bool
    {
        $this->reserved_credits -= $amount;
        return $this->save();
    }

    /**
     * Deduct credits from balance.
     */
    public function deductCredits(int $amount): bool
    {
        if ($this->credits < $amount) {
            return false;
        }

        $this->credits -= $amount;
        return $this->save();
    }

    /**
     * Add credits to balance.
     */
    public function addCredits(int $amount): bool
    {
        $this->credits += $amount;
        return $this->save();
    }
}
