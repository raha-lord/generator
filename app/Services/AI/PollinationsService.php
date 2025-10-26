<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollinationsService
{
    private string $baseUrl = 'https://image.pollinations.ai/prompt';

    /**
     * Generate image using Pollinations.ai API.
     *
     * @param string $prompt
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        try {
            $width = $options['width'] ?? 1024;
            $height = $options['height'] ?? 1024;
            $model = $options['model'] ?? 'flux';
            $seed = $options['seed'] ?? null;
            $enhance = $options['enhance'] ?? false;
            $nologo = $options['nologo'] ?? true;

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
                    'verify' => false, // Disable SSL verification if needed
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
                throw new \Exception('Empty image data received from Pollinations.ai');
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
                'metadata' => [
                    'prompt' => $prompt,
                    'model' => $model,
                    'width' => $width,
                    'height' => $height,
                    'seed' => $seed,
                    'enhanced' => $enhance,
                ],
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
     *
     * @param string $mimeType
     * @return string
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
     * Get available models.
     *
     * @return array<string, string>
     */
    public function getAvailableModels(): array
    {
        return [
            'flux' => 'Flux (Default)',
            'flux-realism' => 'Flux Realism',
            'turbo' => 'Turbo (Fast)',
        ];
    }
}
