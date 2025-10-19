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
            // Enhance prompt for infographic generation
            $enhancedPrompt = $this->enhancePrompt($prompt);

            // Call Gemini API
            $response = $this->geminiService->generateContent($enhancedPrompt, $options);

            // Extract text content
            $generatedText = $this->geminiService->extractText($response);

            if (empty($generatedText)) {
                throw new \Exception('Empty response from Gemini API');
            }

            return [
                'success' => true,
                'data' => [
                    'content' => $generatedText,
                    'prompt' => $prompt,
                    'enhanced_prompt' => $enhancedPrompt,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('InfographicGenerator error', [
                'message' => $e->getMessage(),
                'prompt' => $prompt,
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

    /**
     * Enhance prompt for better infographic generation.
     *
     * @param string $prompt
     * @return string
     */
    private function enhancePrompt(string $prompt): string
    {
        return "Create a detailed infographic content based on the following topic. "
            . "Provide structured information that can be visualized as an infographic:\n\n"
            . $prompt;
    }
}
