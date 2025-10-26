<?php

namespace App\Exceptions;

use Exception;

class PricingNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(
        string $message = 'No pricing configuration found for the requested service',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Service temporarily unavailable',
                'message' => 'Pricing configuration is missing. Please contact support.',
            ], 503);
        }

        return response()->view('errors.pricing-not-found', [], 503);
    }
}
