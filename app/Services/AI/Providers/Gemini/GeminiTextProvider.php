<?php

namespace App\Services\AI\Providers\Gemini;

use App\Services\AI\Contracts\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiTextProvider implements AIProvider
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    private string $modelName = 'gemini-2.0-flash';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
    }

    /**
     * Build context in Gemini format.
     */
    public function buildContext(array $rawMessages): array
    {
        $contents = [];

        foreach ($rawMessages as $msg) {
            // Gemini uses 'model' instead of 'assistant'
            $role = $msg['role'] === 'assistant' ? 'model' : 'user';

            // Skip system messages for now (Gemini handles them differently)
            if ($msg['role'] === 'system') {
                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]]
            ];
        }

        return ['contents' => $contents];
    }

    /**
     * Generate text content using Gemini.
     */
    public function generate(string $prompt, array $context, array $config = []): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured');
        }

        try {
            // Add the new prompt to context
            $context['contents'][] = [
                'role' => 'user',
                'parts' => [['text' => $prompt]]
            ];

            // Merge generation config
            $context['generationConfig'] = array_merge([
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ], $config);

            Log::info('Gemini Text API request', [
                'prompt_length' => strlen($prompt),
                'context_messages' => count($context['contents']),
            ]);

            $response = Http::timeout(120)
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, $context);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $response->body();

                Log::error('Gemini Text API error', [
                    'status' => $response->status(),
                    'error' => $errorBody,
                ]);

                throw new \Exception('Gemini API request failed: ' . $errorMessage);
            }

            $result = $response->json();

            if (empty($result['candidates'])) {
                throw new \Exception('No candidates in Gemini response');
            }

            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($text)) {
                throw new \Exception('Empty response from Gemini');
            }

            Log::info('Gemini Text API response received', [
                'response_length' => strlen($text),
            ]);

            return $text;
        } catch (\Exception $e) {
            Log::error('Gemini Text service error', [
                'message' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100) . '...',
            ]);
            throw $e;
        }
    }

    /**
     * Get token limit for Gemini.
     */
    public function getTokenLimit(): int
    {
        return 1_000_000; // Gemini 2.0 Flash has 1M token context
    }

    /**
     * Get model type.
     */
    public function getModelType(): string
    {
        return 'text';
    }

    /**
     * Estimate tokens (rough approximation).
     */
    public function estimateTokens(string $content): int
    {
        // Rough estimation: ~4 characters per token
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
