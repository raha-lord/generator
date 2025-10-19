<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Balance;
use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BalanceService
{
    /**
     * Deduct credits from user balance.
     *
     * @param User $user
     * @param int $amount
     * @param string $description
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return bool
     */
    public function deduct(
        User $user,
        int $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): bool {
        return DB::transaction(function () use ($user, $amount, $description, $referenceType, $referenceId) {
            $balance = $user->balance;

            if (!$balance) {
                Log::error('Balance not found for user', ['user_id' => $user->id]);
                return false;
            }

            if (!$balance->hasEnoughCredits($amount)) {
                Log::warning('Insufficient credits', [
                    'user_id' => $user->id,
                    'required' => $amount,
                    'available' => $balance->available_credits,
                ]);
                return false;
            }

            $balanceBefore = $balance->credits;

            if (!$balance->deductCredits($amount)) {
                return false;
            }

            // Create transaction record
            BalanceTransaction::createTransaction(
                $user->id,
                'debit',
                $amount,
                $balanceBefore,
                $balance->credits,
                $description,
                $referenceType,
                $referenceId
            );

            return true;
        });
    }

    /**
     * Add credits to user balance.
     *
     * @param User $user
     * @param int $amount
     * @param string $description
     * @param string $type
     * @return bool
     */
    public function add(
        User $user,
        int $amount,
        string $description,
        string $type = 'credit'
    ): bool {
        return DB::transaction(function () use ($user, $amount, $description, $type) {
            $balance = $user->balance;

            if (!$balance) {
                Log::error('Balance not found for user', ['user_id' => $user->id]);
                return false;
            }

            $balanceBefore = $balance->credits;

            if (!$balance->addCredits($amount)) {
                return false;
            }

            // Create transaction record
            BalanceTransaction::createTransaction(
                $user->id,
                $type,
                $amount,
                $balanceBefore,
                $balance->credits,
                $description
            );

            return true;
        });
    }

    /**
     * Refund credits to user.
     *
     * @param User $user
     * @param int $amount
     * @param string $description
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return bool
     */
    public function refund(
        User $user,
        int $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): bool {
        return DB::transaction(function () use ($user, $amount, $description, $referenceType, $referenceId) {
            $balance = $user->balance;

            if (!$balance) {
                return false;
            }

            $balanceBefore = $balance->credits;

            if (!$balance->addCredits($amount)) {
                return false;
            }

            BalanceTransaction::createTransaction(
                $user->id,
                'refund',
                $amount,
                $balanceBefore,
                $balance->credits,
                $description,
                $referenceType,
                $referenceId
            );

            return true;
        });
    }
}
