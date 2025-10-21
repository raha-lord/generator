<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $textModelUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    private string $imageModelUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-preview-image-generation:generateContent';
    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
    }

    /**
     * Generate content using Gemini API.
     *
     * @param string $prompt
     * @param array<string, mixed> $options
     * @param bool $generateImage Use image generation model
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function generateContent(string $prompt, array $options = [], bool $generateImage = false): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured');
        }

        $apiUrl = $generateImage ? $this->imageModelUrl : $this->textModelUrl;

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
                'generate_image' => $generateImage,
                'model' => $generateImage ? 'image' : 'text',
            ]);

            $response = Http::timeout(120)
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, $payload);

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
     * Extract image data from Gemini image response.
     *
     * @param array<string, mixed> $response
     * @return string|null Base64 encoded image data
     */
    public function extractImageData(array $response): ?string
    {
        if (empty($response['candidates'])) {
            Log::warning('No candidates in Gemini image response');
            return null;
        }

        foreach ($response['candidates'][0]['content']['parts'] ?? [] as $part) {
            if (isset($part['inlineData']['data'])) {
                return $part['inlineData']['data'];
            }
        }

        Log::warning('No image data found in response', ['response' => $response]);
        return null;
    }

    /**
     * Get MIME type from image response.
     *
     * @param array<string, mixed> $response
     * @return string
     */
    public function extractImageMimeType(array $response): string
    {
        if (empty($response['candidates'])) {
            return 'image/png';
        }

        foreach ($response['candidates'][0]['content']['parts'] ?? [] as $part) {
            if (isset($part['inlineData']['mimeType'])) {
                return $part['inlineData']['mimeType'];
            }
        }

        return 'image/png';
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
     * Generate infographic image using Gemini.
     *
     * @param string $topic
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function generateInfographicImage(string $topic, array $options = []): array
    {
        $style = $options['style'] ?? 'professional';

        $imagePrompt = $this->buildImagePrompt($topic, $style);

        $response = $this->generateContent($imagePrompt, [
            'temperature' => 0.9,
        ], true); // true = use image model

        $imageData = $this->extractImageData($response);
        $mimeType = $this->extractImageMimeType($response);

        if (empty($imageData)) {
            throw new \Exception('No image data received from Gemini');
        }

        return [
            'image_data' => $imageData,
            'mime_type' => $mimeType,
            'format' => $this->getMimeTypeExtension($mimeType),
            'metadata' => [
                'topic' => $topic,
                'style' => $style,
                'model' => 'gemini-2.5-flash-image',
            ],
        ];
    }

    /**
     * Get file extension from MIME type.
     *
     * @param string $mimeType
     * @return string
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

    /**
     * Build prompt for image generation.
     *
     * @param string $topic
     * @param string $style
     * @return string
     */
    private function buildImagePrompt(string $topic, string $style): string
    {
        $styleDescriptions = [
            'modern' => 'modern design with bold colors, clean lines, geometric shapes, and contemporary aesthetics',
            'classic' => 'classic design with traditional typography, balanced composition, elegant details, and timeless style',
            'minimalist' => 'minimalist design with simple shapes, limited color palette, abundant whitespace, and clean layout',
            'colorful' => 'colorful design with vibrant colors, playful elements, dynamic composition, and eye-catching visuals',
            'professional' => 'professional design with corporate colors, clear hierarchy, structured layout, and business-appropriate style',
        ];

        $styleDesc = $styleDescriptions[$style] ?? $styleDescriptions['professional'];

        return "Create a high-quality infographic image about: {$topic}\n\n"
            . "Style: {$styleDesc}\n\n"
            . "The infographic should be visually appealing, informative, and easy to understand. "
            . "Include relevant icons, charts, statistics, and visual elements that enhance the message. "
            . "Use appropriate typography and color scheme that matches the {$style} style.";
    }
}
