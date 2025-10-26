<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Models\Pricing\AiProvider;
use App\Services\AI\AIServiceInterface;
use App\Services\AI\Concerns\HasPricing;
use App\Services\AI\Contracts\PricingAwareInterface;
use App\Services\AI\PollinationsService;
use Illuminate\Support\Facades\Log;

class ImageGenerator implements AIServiceInterface, PricingAwareInterface
{
    use HasPricing;

    private PollinationsService $pollinationsService;

    public function __construct()
    {
        $this->pollinationsService = new PollinationsService();
    }

    /**
     * Generate image based on prompt.
     *
     * @param string $prompt
     * @param array<string, mixed> $options
     * @return array{success: bool, data?: array<string, mixed>, error?: string}
     */
    public function generate(string $prompt, array $options = []): mixed
    {
        try {
            Log::info('Starting image generation', [
                'prompt' => substr($prompt, 0, 100),
                'options' => $options,
            ]);

            // Generate image using Pollinations.ai
            $result = $this->pollinationsService->generateImage($prompt, $options);

            if (empty($result['image_data'])) {
                throw new \Exception('Empty image data from Pollinations.ai API');
            }

            Log::info('Image generation successful', [
                'mime_type' => $result['mime_type'],
                'format' => $result['format'],
                'model' => $result['metadata']['model'] ?? 'unknown',
            ]);

            return [
                'success' => true,
                'data' => [
                    'image_data' => $result['image_data'],
                    'mime_type' => $result['mime_type'],
                    'format' => $result['format'],
                    'metadata' => $result['metadata'],
                    'prompt' => $prompt,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('ImageGenerator error', [
                'message' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate multiple images in batch.
     *
     * @param string $prompt
     * @param int $count
     * @param array<string, mixed> $options
     * @return array Array of generation results
     */
    public function generateBatch(string $prompt, int $count, array $options = []): array
    {
        Log::info('Starting batch image generation', [
            'prompt' => substr($prompt, 0, 100),
            'count' => $count,
            'options' => $options,
        ]);

        $results = [];

        // Pollinations doesn't support native batch, so we make sequential requests
        for ($i = 0; $i < $count; $i++) {
            Log::info("Generating image {$i}/{$count}");
            $result = $this->generate($prompt, $options);
            $results[] = $result;

            // If any generation fails, continue but log it
            if (!$result['success']) {
                Log::warning("Batch generation failed for image {$i}/{$count}", [
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }
        }

        return $results;
    }

    /**
     * Pollinations doesn't support native batch generation.
     *
     * @return bool
     */
    public function supportsBatchGeneration(): bool
    {
        return false;
    }

    /**
     * Get service type.
     *
     * @return string
     */
    public function getServiceType(): string
    {
        return 'image';
    }

    /**
     * Get available models.
     *
     * @return array<string, string>
     */
    public function getAvailableModels(): array
    {
        return $this->pollinationsService->getAvailableModels();
    }

    /**
     * Get AI provider ID
     *
     * @return int
     */
    public function getProviderId(): int
    {
        return AiProvider::POLLINATIONS;
    }

    /**
     * Get pricing parameters for the request
     *
     * @param array $requestData
     * @return array
     */
    public function getPricingParameters(array $requestData): array
    {
        $width = $requestData['width'] ?? 512;
        $height = $requestData['height'] ?? 512;
        $model = $requestData['model'] ?? 'flux';

        return [
            'resolution' => "{$width}x{$height}",
            'model' => $model,
            'enhance' => $requestData['enhance'] ?? false,
        ];
    }
}
