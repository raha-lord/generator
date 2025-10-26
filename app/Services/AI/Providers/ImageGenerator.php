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
