<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Gemini provider ID
        $geminiProvider = DB::table('pricing.ai_providers')
            ->where('code', 'gemini')
            ->first();

        if (!$geminiProvider) {
            $this->command->warn('Gemini provider not found in pricing.ai_providers. Please run pricing seeders first.');
            return;
        }

        // Create Text Chat service
        $textChatService = DB::table('chat.services')->insertGetId([
            'code' => 'text_chat',
            'name' => 'Text Chat',
            'description' => 'Simple text conversation with AI',
            'type' => 'simple',
            'icon' => '💬',
            'color' => '#3B82F6',
            'is_active' => true,
            'config' => json_encode([
                'temperature' => 0.7,
                'max_tokens' => 2048,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create workflow step for Text Chat
        DB::table('chat.workflow_steps')->insert([
            'service_id' => $textChatService,
            'order' => 1,
            'code' => 'generate_response',
            'name' => 'Generate Response',
            'model_type' => 'text',
            'provider_id' => $geminiProvider->id,
            'requires_confirmation' => false,
            'prompt_template' => null, // Direct user input
            'config' => json_encode([
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✓ Text Chat service created');

        // Create Image Generation service
        $pollinationsProvider = DB::table('pricing.ai_providers')
            ->where('code', 'pollinations')
            ->first();

        if ($pollinationsProvider) {
            $imageGenService = DB::table('chat.services')->insertGetId([
                'code' => 'image_generation',
                'name' => 'Image Generation',
                'description' => 'Generate images from text prompts',
                'type' => 'simple',
                'icon' => '🎨',
                'color' => '#8B5CF6',
                'is_active' => true,
                'config' => json_encode([
                    'default_width' => 1024,
                    'default_height' => 1024,
                    'default_model' => 'flux',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('chat.workflow_steps')->insert([
                'service_id' => $imageGenService,
                'order' => 1,
                'code' => 'generate_image',
                'name' => 'Generate Image',
                'model_type' => 'image',
                'provider_id' => $pollinationsProvider->id,
                'requires_confirmation' => false,
                'prompt_template' => null,
                'config' => json_encode([
                    'width' => 1024,
                    'height' => 1024,
                    'model' => 'flux',
                    'enhance' => false,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✓ Image Generation service created');
        }

        // Create Multi-step Infographic service
        if ($geminiProvider && $pollinationsProvider) {
            $infographicService = DB::table('chat.services')->insertGetId([
                'code' => 'infographic_creation',
                'name' => 'Infographic Creation',
                'description' => 'Create infographics with AI - generate structure then visualize',
                'type' => 'multi_step',
                'icon' => '📊',
                'color' => '#10B981',
                'is_active' => true,
                'config' => json_encode([
                    'default_style' => 'professional',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Step 1: Generate text structure
            DB::table('chat.workflow_steps')->insert([
                'service_id' => $infographicService,
                'order' => 1,
                'code' => 'generate_structure',
                'name' => 'Generate Infographic Structure',
                'model_type' => 'text',
                'provider_id' => $geminiProvider->id,
                'requires_confirmation' => true,
                'prompt_template' => 'Create a detailed infographic structure for: {user_input}. Include title, key points, statistics, and visual descriptions.',
                'config' => json_encode([
                    'temperature' => 0.8,
                    'maxOutputTokens' => 2048,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Step 2: Generate image
            DB::table('chat.workflow_steps')->insert([
                'service_id' => $infographicService,
                'order' => 2,
                'code' => 'generate_visual',
                'name' => 'Generate Visual',
                'model_type' => 'image',
                'provider_id' => $pollinationsProvider->id,
                'requires_confirmation' => false,
                'prompt_template' => 'Create a professional infographic image based on: {user_input}',
                'config' => json_encode([
                    'width' => 1024,
                    'height' => 1536,
                    'model' => 'flux',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✓ Infographic Creation service created (multi-step)');
        }

        $this->command->info('');
        $this->command->info('Chat services seeded successfully! 🚀');
    }
}
