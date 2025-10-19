<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AI\GeminiService;
use Illuminate\Console\Command;

class TestGeminiApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:test {prompt? : The prompt to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Gemini API connection and generation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing Gemini API...');
        $this->newLine();

        // Check API key
        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) {
            $this->error('❌ GEMINI_API_KEY is not configured in .env file');
            $this->newLine();
            $this->line('Please add your Gemini API key to the .env file:');
            $this->line('GEMINI_API_KEY=your_api_key_here');
            return self::FAILURE;
        }

        $this->info('✓ API Key is configured');
        $this->newLine();

        // Get prompt
        $prompt = $this->argument('prompt') ?? 'Write a short greeting message';

        $this->line("Testing with prompt: <comment>{$prompt}</comment>");
        $this->newLine();

        try {
            $geminiService = new GeminiService();

            $this->info('Sending request to Gemini API...');
            $startTime = microtime(true);

            $response = $geminiService->generateContent($prompt);

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            $this->info("✓ Request completed in {$duration}ms");
            $this->newLine();

            $text = $geminiService->extractText($response);

            if (empty($text)) {
                $this->error('❌ Empty response from Gemini API');
                $this->line('Full response:');
                $this->line(json_encode($response, JSON_PRETTY_PRINT));
                return self::FAILURE;
            }

            $this->line('<info>Generated content:</info>');
            $this->newLine();
            $this->line($text);
            $this->newLine();

            $this->info('✓ Gemini API is working correctly!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->newLine();
            $this->line('Trace:');
            $this->line($e->getTraceAsString());

            return self::FAILURE;
        }
    }
}
