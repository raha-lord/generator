<?php

namespace App\Exceptions;

use Exception;

class ProviderInactiveException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(
        string $message = 'AI provider is inactive or not available',
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
                'error' => 'Service unavailable',
                'message' => 'The requested AI provider is currently unavailable.',
            ], 503);
        }

        return response()->view('errors.provider-inactive', [], 503);
    }
}
