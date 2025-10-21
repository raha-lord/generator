<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Providers\InfographicGenerator;
use InvalidArgumentException;

class AIServiceFactory
{
    /**
     * Create an AI service instance based on type.
     *
     * @param string $type Service type ('infographic', 'text', 'description')
     * @return AIServiceInterface
     * @throws InvalidArgumentException
     */
    public static function make(string $type): AIServiceInterface
    {
        return match ($type) {
            'infographic' => new InfographicGenerator(),
            // Future services (заглушки пока не реализованы):
            // 'text' => new TextGenerator(),
            // 'description' => new DescriptionGenerator(),
            default => throw new InvalidArgumentException("Unknown AI service type: {$type}"),
        };
    }

    /**
     * Get all available service types.
     *
     * @return array<string>
     */
    public static function getAvailableTypes(): array
    {
        return [
            'infographic',
            // 'text',
            // 'description',
        ];
    }
}
