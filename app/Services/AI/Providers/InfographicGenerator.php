<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Models\Pricing\AiProvider;
use App\Services\AI\AIServiceInterface;
use App\Services\AI\Concerns\HasPricing;
use App\Services\AI\Contracts\PricingAwareInterface;
use App\Services\AI\GeminiService;
use App\Services\AI\PollinationsService;
use Illuminate\Support\Facades\Log;

class InfographicGenerator implements AIServiceInterface, PricingAwareInterface
{
    use HasPricing;

    private GeminiService|PollinationsService $imageService;
    private int $providerId;

    public function __construct(?int $providerId = null)
    {
        // Default to Gemini if no provider specified
        $this->providerId = $providerId ?? AiProvider::GEMINI;

        // Initialize the appropriate service based on provider
        $this->imageService = match($this->providerId) {
            AiProvider::POLLINATIONS => new PollinationsService(),
            AiProvider::GEMINI => new GeminiService(),
            default => new GeminiService(),
        };
    }

    /**
     * Generate single infographic slide.
     *
     * @param string $prompt
     * @param array<string, mixed> $options
     * @return array{success: bool, data?: array<string, mixed>, error?: string}
     */
    public function generate(string $prompt, array $options = []): mixed
    {
        try {
            Log::info('Starting infographic slide generation', [
                'prompt' => substr($prompt, 0, 100),
                'provider' => $this->providerId,
                'options' => $options,
            ]);

            // Generate using the appropriate service
            if ($this->imageService instanceof GeminiService) {
                $result = $this->imageService->generateInfographicImage($prompt, $options);
            } else {
                // PollinationsService generates regular images
                $result = $this->imageService->generateImage($prompt, $options);
            }

            if (empty($result['image_data'])) {
                throw new \Exception('Empty image data from API');
            }

            Log::info('Infographic slide generation successful', [
                'mime_type' => $result['mime_type'],
                'format' => $result['format'],
                'provider' => $this->providerId,
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
                'provider' => $this->providerId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate multiple infographic slides in batch.
     *
     * @param string $prompt
     * @param int $count
     * @param array<string, mixed> $options
     * @return array Array of generation results
     */
    public function generateBatch(string $prompt, int $count, array $options = []): array
    {
        Log::info('Starting batch infographic generation', [
            'prompt' => substr($prompt, 0, 100),
            'count' => $count,
            'provider' => $this->providerId,
            'options' => $options,
        ]);

        $results = [];

        // Neither Gemini nor Pollinations support native batch for infographics
        // So we make sequential requests
        for ($i = 0; $i < $count; $i++) {
            Log::info("Generating infographic slide " . ($i + 1) . "/{$count}");

            // Optionally modify prompt for each slide
            $slidePrompt = $count > 1
                ? "{$prompt} (Slide " . ($i + 1) . " of {$count})"
                : $prompt;

            $result = $this->generate($slidePrompt, $options);
            $results[] = $result;

            // If any generation fails, continue but log it
            if (!$result['success']) {
                Log::warning("Batch generation failed for slide " . ($i + 1) . "/{$count}", [
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }
        }

        return $results;
    }

    /**
     * Neither Gemini nor Pollinations support native batch for infographics.
     *
     * @return bool
     */
    public function supportsBatchGeneration(): bool
    {
        return false;
    }

    /**
     * Get service type.
     * Infographics are images, so service_type is 'image'.
     *
     * @return string
     */
    public function getServiceType(): string
    {
        return 'image';
    }

    /**
     * Get AI provider ID
     *
     * @return int
     */
    public function getProviderId(): int
    {
        return $this->providerId;
    }

    /**
     * Get pricing parameters for the request
     *
     * @param array $requestData
     * @return array
     */
    public function getPricingParameters(array $requestData): array
    {
        $width = $requestData['width'] ?? 1024;
        $height = $requestData['height'] ?? 1024;

        $params = [
            'resolution' => "{$width}x{$height}",
        ];

        // Add provider-specific parameters
        if ($this->providerId === AiProvider::POLLINATIONS) {
            $params['model'] = $requestData['model'] ?? 'flux';
            $params['enhance'] = $requestData['enhance'] ?? false;
        } elseif ($this->providerId === AiProvider::GEMINI) {
            $params['complexity'] = $requestData['complexity'] ?? 'standard';
        }

        return $params;
    }
}
