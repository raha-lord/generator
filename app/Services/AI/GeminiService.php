<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

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
            $payload = [
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
                    'maxOutputTokens' => 2048,
                ], $options),
            ];

            Log::info('Gemini API request', [
                'prompt_length' => strlen($prompt),
            ]);

            $response = Http::timeout(60)
                ->post($this->apiUrl . '?key=' . $this->apiKey, $payload);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $response->body();

                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'error' => $errorBody,
                ]);

                throw new \Exception('Gemini API request failed: ' . $errorMessage);
            }

            $result = $response->json();

            Log::info('Gemini API response received', [
                'has_candidates' => !empty($result['candidates']),
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Gemini service error', [
                'message' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100) . '...',
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
        if (empty($response['candidates'])) {
            Log::warning('No candidates in Gemini response', ['response' => $response]);
            return '';
        }

        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            Log::warning('Empty text in Gemini response', ['response' => $response]);
        }

        return $text;
    }

    /**
     * Generate structured infographic content.
     *
     * @param string $topic
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function generateInfographicContent(string $topic, array $options = []): array
    {
        $style = $options['style'] ?? 'professional';
        $format = $options['format'] ?? 'png';

        $enhancedPrompt = $this->buildInfographicPrompt($topic, $style);

        $response = $this->generateContent($enhancedPrompt, [
            'temperature' => 0.8,
            'maxOutputTokens' => 2048,
        ]);

        $content = $this->extractText($response);

        return [
            'content' => $content,
            'format' => $format,
            'metadata' => [
                'topic' => $topic,
                'style' => $style,
                'model' => 'gemini-2.0-flash',
            ],
        ];
    }

    /**
     * Build enhanced prompt for infographic generation.
     *
     * @param string $topic
     * @param string $style
     * @return string
     */
    private function buildInfographicPrompt(string $topic, string $style): string
    {
        $styleDescriptions = [
            'modern' => 'using bold colors, clean lines, and contemporary design elements',
            'classic' => 'using traditional typography, balanced layouts, and timeless aesthetics',
            'minimalist' => 'using simple shapes, limited color palette, and abundant whitespace',
            'colorful' => 'using vibrant colors, playful elements, and dynamic compositions',
            'professional' => 'using corporate colors, clear hierarchy, and business-appropriate design',
        ];

        $styleDesc = $styleDescriptions[$style] ?? $styleDescriptions['professional'];

        return <<<PROMPT
Create a detailed structured content for an infographic about: {$topic}

Style: {$style} ({$styleDesc})

Please provide the content in the following format:

# TITLE
[Main title of the infographic]

## KEY POINTS
[List 3-5 main points, each with a brief explanation]

## STATISTICS/DATA
[Include 2-4 relevant statistics or data points with sources if applicable]

## VISUAL ELEMENTS DESCRIPTION
[Describe icons, charts, or visual metaphors that would enhance the infographic]

## CALL TO ACTION
[A compelling call to action or key takeaway]

## COLOR SCHEME
[Suggest 3-4 colors that fit the {$style} style]

Make the content informative, engaging, and visually descriptive.
PROMPT;
    }
}
