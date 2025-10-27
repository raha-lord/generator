<?php

namespace App\Services\AI\Providers\Pollinations;

use App\Services\AI\Contracts\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollinationsImageProvider implements AIProvider
{
    private string $baseUrl = 'https://image.pollinations.ai/prompt';
    private string $modelName = 'flux';

    public function __construct(?string $model = null)
    {
        if ($model) {
            $this->modelName = $model;
        }
    }

    /**
     * Build context (image models typically don't use conversation history).
     */
    public function buildContext(array $rawMessages): array
    {
        return [];
    }

    /**
     * Generate image using Pollinations.
     * Returns array with base64 image data and metadata.
     */
    public function generate(string $prompt, array $context, array $config = []): array
    {
        try {
            $width = $config['width'] ?? 1024;
            $height = $config['height'] ?? 1024;
            $model = $config['model'] ?? $this->modelName;
            $seed = $config['seed'] ?? null;
            $enhance = $config['enhance'] ?? false;
            $nologo = $config['nologo'] ?? true;

            // Build query parameters
            $queryParams = [
                'width' => $width,
                'height' => $height,
                'model' => $model,
                'nologo' => $nologo ? 'true' : 'false',
            ];

            if ($enhance) {
                $queryParams['enhance'] = 'true';
            }

            if ($seed !== null) {
                $queryParams['seed'] = $seed;
            }

            // URL encode the prompt
            $encodedPrompt = urlencode($prompt);
            $url = "{$this->baseUrl}/{$encodedPrompt}";

            Log::info('Pollinations API request', [
                'prompt_length' => strlen($prompt),
                'width' => $width,
                'height' => $height,
                'model' => $model,
                'seed' => $seed,
                'enhance' => $enhance,
            ]);

            // Make request to Pollinations.ai
            $response = Http::timeout(120)
                ->withOptions([
                    'verify' => false,
                ])
                ->get($url, $queryParams);

            if (!$response->successful()) {
                Log::error('Pollinations API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Pollinations API request failed: HTTP ' . $response->status());
            }

            // Get image binary data
            $imageData = $response->body();

            if (empty($imageData)) {
                throw new \Exception('Empty image data received from Pollinations');
            }

            // Detect image format from response headers
            $contentType = $response->header('Content-Type') ?? 'image/png';
            $format = $this->getMimeTypeExtension($contentType);

            Log::info('Pollinations API response received', [
                'content_type' => $contentType,
                'size' => strlen($imageData),
                'format' => $format,
            ]);

            return [
                'image_data' => base64_encode($imageData),
                'mime_type' => $contentType,
                'format' => $format,
                'width' => $width,
                'height' => $height,
                'seed' => $seed,
            ];
        } catch (\Exception $e) {
            Log::error('Pollinations service error', [
                'message' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100) . '...',
            ]);
            throw $e;
        }
    }

    /**
     * Get file extension from MIME type.
     */
    private function getMimeTypeExtension(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'png',
        };
    }

    /**
     * Get token limit (not applicable for image models).
     */
    public function getTokenLimit(): int
    {
        return 1000; // Pollinations has simple prompt limits
    }

    /**
     * Get model type.
     */
    public function getModelType(): string
    {
        return 'image';
    }

    /**
     * Estimate tokens (simple character count for images).
     */
    public function estimateTokens(string $content): int
    {
        return (int) ceil(strlen($content) / 4);
    }

    /**
     * Get provider name.
     */
    public function getProviderName(): string
    {
        return 'pollinations';
    }

    /**
     * Get model name.
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }

    /**
     * Get available models.
     */
    public static function getAvailableModels(): array
    {
        return [
            'flux' => 'Flux (Default)',
            'flux-realism' => 'Flux Realism',
            'turbo' => 'Turbo (Fast)',
        ];
    }
}
