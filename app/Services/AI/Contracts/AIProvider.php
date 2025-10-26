<?php

namespace App\Services\AI\Contracts;

interface AIProvider
{
    /**
     * Format raw messages into provider-specific context format.
     *
     * @param array $rawMessages Array of messages with 'role' and 'content' keys
     * @return array Formatted context for the provider's API
     */
    public function buildContext(array $rawMessages): array;

    /**
     * Generate content using the AI model.
     *
     * @param string $prompt The user's prompt/input
     * @param array $context The conversation context (formatted by buildContext)
     * @param array $config Additional configuration (temperature, max_tokens, etc.)
     * @return mixed Generated content (string, array, or other format)
     */
    public function generate(string $prompt, array $context, array $config = []): mixed;

    /**
     * Get the maximum token limit for this provider.
     *
     * @return int Maximum number of tokens
     */
    public function getTokenLimit(): int;

    /**
     * Get the model type (text, image, audio, video).
     *
     * @return string Model type
     */
    public function getModelType(): string;

    /**
     * Estimate the number of tokens in a given content.
     *
     * @param string $content The content to estimate tokens for
     * @return int Estimated number of tokens
     */
    public function estimateTokens(string $content): int;

    /**
     * Get the provider name.
     *
     * @return string Provider name (e.g., 'openai', 'gemini', 'claude')
     */
    public function getProviderName(): string;

    /**
     * Get the model name being used.
     *
     * @return string Model name (e.g., 'gpt-4', 'gemini-2.0-flash')
     */
    public function getModelName(): string;
}
