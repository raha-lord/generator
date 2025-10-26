<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Models\Pricing\AiProvider;
use App\Services\AI\AIServiceInterface;
use App\Services\AI\Concerns\HasPricing;
use App\Services\AI\Contracts\PricingAwareInterface;
use App\Services\AI\GeminiService;
use Illuminate\Support\Facades\Log;

class InfographicGenerator implements AIServiceInterface, PricingAwareInterface
{
    use HasPricing;

    private GeminiService $geminiService;

    public function __construct()
    {
        $this->geminiService = new GeminiService();
    }

    /**
     * Generate infographic based on prompt.
     *
     * @param string $prompt
     * @param array<string, mixed> $options
     * @return array{success: bool, data?: array<string, mixed>, error?: string}
     */
    public function generate(string $prompt, array $options = []): mixed
    {
        try {
            Log::info('Starting infographic image generation', [
                'prompt' => substr($prompt, 0, 100),
                'options' => $options,
            ]);

            // Generate actual infographic image using Gemini
            $result = $this->geminiService->generateInfographicImage($prompt, $options);

            if (empty($result['image_data'])) {
                throw new \Exception('Empty image data from Gemini API');
            }

            Log::info('Infographic image generation successful', [
                'mime_type' => $result['mime_type'],
                'format' => $result['format'],
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
            Log::error('InfographicGenerator error', [
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
        return 'infographic';
    }

    /**
     * Get AI provider ID
     *
     * @return int
     */
    protected function getProviderId(): int
    {
        return AiProvider::GEMINI;
    }

    /**
     * Get pricing parameters for the request
     *
     * @param array $requestData
     * @return array
     */
    protected function getPricingParameters(array $requestData): array
    {
        $width = $requestData['width'] ?? 1024;
        $height = $requestData['height'] ?? 1024;

        return [
            'resolution' => "{$width}x{$height}",
            'complexity' => $requestData['complexity'] ?? 'standard',
        ];
    }
}
