<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Providers\InfographicGenerator;
use App\Services\AI\Providers\ImageGenerator;
use InvalidArgumentException;

class AIServiceFactory
{
    /**
     * Create an AI service instance based on type.
     *
     * @param string $type Service type ('infographic', 'image', 'text', 'description')
     * @param array<string, mixed> $config Configuration options (e.g., ['provider_id' => 1])
     * @return AIServiceInterface
     * @throws InvalidArgumentException
     */
    public static function make(string $type, array $config = []): AIServiceInterface
    {
        return match ($type) {
            'infographic' => new InfographicGenerator($config['provider_id'] ?? null),
            'image' => new ImageGenerator(),
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
            'image',
            // 'text',
            // 'description',
        ];
    }
}
