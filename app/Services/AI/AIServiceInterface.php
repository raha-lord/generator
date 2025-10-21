<?php

declare(strict_types=1);

namespace App\Services\AI;

interface AIServiceInterface
{
    /**
     * Generate content based on the prompt.
     *
     * @param string $prompt The generation prompt
     * @param array<string, mixed> $options Additional options
     * @return mixed The generation result
     */
    public function generate(string $prompt, array $options = []): mixed;

    /**
     * Get the service type identifier.
     *
     * @return string Service type (e.g., 'infographic', 'text', 'description')
     */
    public function getServiceType(): string;

    /**
     * Get the cost in credits for this service.
     *
     * @return int Cost in credits
     */
    public function getCost(): int;
}
