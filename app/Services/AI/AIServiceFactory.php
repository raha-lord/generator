<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\Pricing\AiProvider;
use App\Services\AI\Contracts\AIProvider as AIProviderContract;
use App\Services\AI\Providers\Gemini\GeminiImageProvider;
use App\Services\AI\Providers\Gemini\GeminiTextProvider;
use App\Services\AI\Providers\Pollinations\PollinationsImageProvider;

class AIServiceFactory
{
    /**
     * Provider class mapping.
     * Format: 'provider_code.model_type' => ProviderClass::class
     */
    private static array $providerMap = [
        // Gemini providers
        'gemini.text' => GeminiTextProvider::class,
        'gemini.image' => GeminiImageProvider::class,

        // Pollinations providers
        'pollinations.image' => PollinationsImageProvider::class,

        // OpenAI providers (to be implemented)
        // 'openai.text' => OpenAITextProvider::class,
        // 'openai.image' => OpenAIImageProvider::class,

        // Claude providers (to be implemented)
        // 'claude.text' => ClaudeTextProvider::class,
    ];

    /**
     * Create an AI provider instance.
     *
     * @param string $modelType The type of model (text, image, audio, video)
     * @param int|null $providerId The provider ID from pricing.ai_providers table
     * @param array $config Additional configuration
     * @return AIProviderContract
     * @throws \Exception
     */
    public static function make(string $modelType, ?int $providerId = null, array $config = []): AIProviderContract
    {
        // If provider ID is specified, get provider from database
        if ($providerId) {
            $provider = AiProvider::findOrFail($providerId);
            $providerCode = $provider->code;
        } else {
            // Use default provider for model type
            $providerCode = self::getDefaultProvider($modelType);
        }

        $key = "{$providerCode}.{$modelType}";

        if (!isset(self::$providerMap[$key])) {
            throw new \Exception("Provider '{$providerCode}' for model type '{$modelType}' is not available");
        }

        $providerClass = self::$providerMap[$key];

        // Create instance with config
        return app($providerClass, ['config' => $config]);
    }

    /**
     * Get default provider for a model type.
     *
     * @param string $modelType
     * @return string Provider code
     */
    private static function getDefaultProvider(string $modelType): string
    {
        return match($modelType) {
            'text' => 'gemini',
            'image' => 'pollinations',
            'audio' => 'openai', // placeholder
            'video' => 'openai', // placeholder
            default => throw new \Exception("Unknown model type: {$modelType}"),
        };
    }

    /**
     * Check if a provider is available for a model type.
     *
     * @param string $providerCode
     * @param string $modelType
     * @return bool
     */
    public static function isAvailable(string $providerCode, string $modelType): bool
    {
        $key = "{$providerCode}.{$modelType}";
        return isset(self::$providerMap[$key]);
    }

    /**
     * Get all available providers for a model type.
     *
     * @param string $modelType
     * @return array Array of provider codes
     */
    public static function getAvailableProviders(string $modelType): array
    {
        $providers = [];

        foreach (self::$providerMap as $key => $class) {
            [$providerCode, $type] = explode('.', $key);

            if ($type === $modelType) {
                $providers[] = $providerCode;
            }
        }

        return array_unique($providers);
    }

    /**
     * Register a new provider.
     *
     * @param string $providerCode
     * @param string $modelType
     * @param string $providerClass
     */
    public static function register(string $providerCode, string $modelType, string $providerClass): void
    {
        $key = "{$providerCode}.{$modelType}";
        self::$providerMap[$key] = $providerClass;
    }
}
