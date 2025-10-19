<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBalance
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $requiredCredits = 0): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        if (!$user->balance) {
            return redirect()->route('dashboard')
                ->with('error', 'Balance not found. Please contact support.');
        }

        if ($requiredCredits > 0 && !$user->balance->hasEnoughCredits($requiredCredits)) {
            return redirect()->route('dashboard')
                ->with('error', "Insufficient credits. Required: {$requiredCredits}, Available: {$user->balance->available_credits}");
        }

        return $next($request);
    }
}
