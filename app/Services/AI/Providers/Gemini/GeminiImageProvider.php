<?php

namespace App\Services\AI\Providers\Gemini;

use App\Services\AI\Contracts\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiImageProvider implements AIProvider
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-preview-image-generation:generateContent';
    private string $modelName = 'gemini-2.0-flash-image';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
    }

    /**
     * Build context (image models typically don't use conversation history).
     */
    public function buildContext(array $rawMessages): array
    {
        // Image generation typically doesn't use full context
        // But we can extract reference images or previous prompts if needed
        return ['contents' => []];
    }

    /**
     * Generate image using Gemini.
     * Returns array with base64 image data and metadata.
     */
    public function generate(string $prompt, array $context, array $config = []): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured');
        }

        try {
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => array_merge([
                    'temperature' => 0.9,
                ], $config),
            ];

            Log::info('Gemini Image API request', [
                'prompt_length' => strlen($prompt),
            ]);

            $response = Http::timeout(120)
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, $payload);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $response->body();

                Log::error('Gemini Image API error', [
                    'status' => $response->status(),
                    'error' => $errorBody,
                ]);

                throw new \Exception('Gemini Image API request failed: ' . $errorMessage);
            }

            $result = $response->json();

            if (empty($result['candidates'])) {
                throw new \Exception('No candidates in Gemini image response');
            }

            // Extract image data
            $imageData = null;
            $mimeType = 'image/png';

            foreach ($result['candidates'][0]['content']['parts'] ?? [] as $part) {
                if (isset($part['inlineData']['data'])) {
                    $imageData = $part['inlineData']['data'];
                    $mimeType = $part['inlineData']['mimeType'] ?? 'image/png';
                    break;
                }
            }

            if (empty($imageData)) {
                throw new \Exception('No image data in Gemini response');
            }

            Log::info('Gemini Image API response received', [
                'mime_type' => $mimeType,
                'data_length' => strlen($imageData),
            ]);

            return [
                'image_data' => $imageData,
                'mime_type' => $mimeType,
                'format' => $this->getMimeTypeExtension($mimeType),
            ];
        } catch (\Exception $e) {
            Log::error('Gemini Image service error', [
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
        return match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'png',
        };
    }

    /**
     * Get token limit.
     */
    public function getTokenLimit(): int
    {
        return 100_000; // Image models typically have lower limits
    }

    /**
     * Get model type.
     */
    public function getModelType(): string
    {
        return 'image';
    }

    /**
     * Estimate tokens.
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
        return 'gemini';
    }

    /**
     * Get model name.
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }
}
