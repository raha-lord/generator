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
     * @return mixed The generation result (single item)
     */
    public function generate(string $prompt, array $options = []): mixed;

    /**
     * Generate multiple items in batch.
     * Driver decides whether to use single request (if API supports it)
     * or multiple sequential requests.
     *
     * @param string $prompt The generation prompt
     * @param int $count Number of items to generate
     * @param array<string, mixed> $options Additional options
     * @return array Array of generation results
     */
    public function generateBatch(string $prompt, int $count, array $options = []): array;

    /**
     * Check if driver supports native batch generation in a single API request.
     * If false, generateBatch() will make multiple sequential calls to generate().
     *
     * @return bool True if driver can generate multiple items in one API call
     */
    public function supportsBatchGeneration(): bool;

    /**
     * Get the service type identifier.
     *
     * @return string Service type (e.g., 'image', 'text', 'video')
     */
    public function getServiceType(): string;

    /**
     * Get the cost in credits for this service.
     *
     * @param array<string, mixed> $requestData Request parameters for cost calculation
     * @return int Cost in credits
     */
    public function getCost(array $requestData = []): int;
}
