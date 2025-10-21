<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BalanceTransaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related reference model (Generation, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if transaction is credit.
     */
    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    /**
     * Check if transaction is debit.
     */
    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }

    /**
     * Check if transaction is refund.
     */
    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }

    /**
     * Check if transaction is bonus.
     */
    public function isBonus(): bool
    {
        return $this->type === 'bonus';
    }

    /**
     * Create a transaction record.
     */
    public static function createTransaction(
        int $userId,
        string $type,
        int $amount,
        int $balanceBefore,
        int $balanceAfter,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }
}
