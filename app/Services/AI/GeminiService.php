<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
    }

    /**
     * Generate content using Gemini API.
     *
     * @param string $prompt
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function generateContent(string $prompt, array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured');
        }

        try {
            $response = Http::timeout(60)
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => array_merge([
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ], $options),
                ]);

            if (!$response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Gemini API request failed: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Gemini service error', [
                'message' => $e->getMessage(),
                'prompt' => $prompt,
            ]);
            throw $e;
        }
    }

    /**
     * Extract text from Gemini response.
     *
     * @param array<string, mixed> $response
     * @return string
     */
    public function extractText(array $response): string
    {
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
}
