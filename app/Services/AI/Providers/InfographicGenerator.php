<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Services\AI\AIServiceInterface;
use App\Services\AI\GeminiService;
use Illuminate\Support\Facades\Log;

class InfographicGenerator implements AIServiceInterface
{
    private GeminiService $geminiService;
    private int $cost = 10; // Cost in credits as per TZ

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
     * Get generation cost in credits.
     *
     * @return int
     */
    public function getCost(): int
    {
        return $this->cost;
    }
}
