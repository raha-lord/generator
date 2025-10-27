<?php

namespace App\Services\Chat;

use App\Models\Chat\Chat;
use App\Models\Chat\WorkflowStep;
use App\Services\AI\AIServiceFactory;
use App\Services\BalanceService;
use App\Services\Pricing\PricingCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowEngine
{
    public function __construct(
        private ContextStorage $contextStorage,
        private MessageService $messageService,
        private BalanceService $balanceService,
        private PricingCalculator $pricingCalculator
    ) {}

    /**
     * Execute a workflow step for a chat.
     *
     * @param Chat $chat
     * @param string $userInput
     * @return array Result with message and metadata
     * @throws \Exception
     */
    public function executeStep(Chat $chat, string $userInput): array
    {
        // Get current step
        $step = $chat->getCurrentStep();

        if (!$step) {
            throw new \Exception('No workflow step found for current chat state');
        }

        Log::info('Executing workflow step', [
            'chat_id' => $chat->id,
            'step_id' => $step->id,
            'step_name' => $step->name,
            'step_order' => $step->order,
        ]);

        // Save user message
        $userMessage = $this->messageService->createUserMessage($chat, $userInput, $step);

        try {
            DB::beginTransaction();

            // Calculate cost for this step
            $cost = $this->calculateStepCost($step, $userInput);

            // Check user balance
            $balance = $chat->user->balance;
            if (!$balance || !$balance->hasEnoughCredits((int)$cost)) {
                throw new \Exception('Insufficient credits. Required: ' . $cost . ', Available: ' . ($balance->available_credits ?? 0));
            }

            // Get conversation context
            $rawMessages = $this->contextStorage->getMessages($chat);

            // Get AI provider for this step
            $provider = AIServiceFactory::make(
                $step->model_type,
                $step->provider_id,
                $step->config ?? []
            );

            // Build context in provider-specific format
            $formattedContext = $provider->buildContext($rawMessages);

            // Apply prompt template if exists
            $prompt = $step->prompt_template
                ? $step->getPrompt(['user_input' => $userInput])
                : $userInput;

            Log::info('Calling AI provider', [
                'provider' => $provider->getProviderName(),
                'model_type' => $provider->getModelType(),
                'prompt_length' => strlen($prompt),
            ]);

            // Generate response
            $result = $provider->generate($prompt, $formattedContext, $step->config ?? []);

            // Handle different result types
            $responseContent = is_array($result) ? json_encode($result) : $result;

            // Deduct credits (only after successful generation)
            $deducted = $this->balanceService->deduct(
                $chat->user,
                (int)$cost,
                "Chat step: {$step->name}",
                'chat_message',
                null
            );

            if (!$deducted) {
                throw new \Exception('Failed to deduct credits');
            }

            // Save assistant message
            $assistantMessage = $this->messageService->createAssistantMessage(
                chat: $chat,
                content: $responseContent,
                metadata: [
                    'provider' => $provider->getProviderName(),
                    'model' => $provider->getModelName(),
                    'model_type' => $provider->getModelType(),
                    'result_type' => is_array($result) ? 'json' : 'text',
                ],
                step: $step,
                creditsSpent: $cost
            );

            DB::commit();

            Log::info('Workflow step completed', [
                'chat_id' => $chat->id,
                'step_id' => $step->id,
                'credits_spent' => $cost,
            ]);

            return [
                'success' => true,
                'message' => $assistantMessage,
                'result' => $result,
                'credits_spent' => $cost,
                'step' => $step,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Workflow step execution failed', [
                'chat_id' => $chat->id,
                'step_id' => $step->id,
                'error' => $e->getMessage(),
            ]);

            // Mark user message as failed
            $this->messageService->markAsFailed($userMessage, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Move chat to the next step in workflow.
     *
     * @param Chat $chat
     * @return bool True if moved to next step, false if workflow completed
     */
    public function moveToNextStep(Chat $chat): bool
    {
        $currentOrder = $chat->current_step_order;
        $nextStep = $chat->service
            ->workflowSteps()
            ->where('order', $currentOrder + 1)
            ->first();

        if ($nextStep) {
            $chat->moveToNextStep();
            return true;
        }

        // No more steps - mark chat as completed
        $chat->markAsCompleted();
        return false;
    }

    /**
     * Check if current step requires user confirmation.
     *
     * @param Chat $chat
     * @return bool
     */
    public function currentStepRequiresConfirmation(Chat $chat): bool
    {
        $step = $chat->getCurrentStep();
        return $step ? $step->requiresConfirmation() : false;
    }

    /**
     * Calculate cost for executing a step.
     *
     * @param WorkflowStep $step
     * @param string $input
     * @return float
     */
    private function calculateStepCost(WorkflowStep $step, string $input): float
    {
        try {
            // Use pricing calculator to get accurate cost
            $cost = $this->pricingCalculator->calculate(
                serviceType: $step->model_type,
                providerId: $step->provider_id,
                parameters: array_merge(
                    ['prompt' => $input],
                    $step->config ?? []
                )
            );

            return (float) $cost;
        } catch (\Exception $e) {
            Log::warning('Failed to calculate step cost, using fallback', [
                'step_id' => $step->id,
                'error' => $e->getMessage(),
            ]);

            // Fallback to simple estimation
            return match($step->model_type) {
                'text' => 5.0,
                'image' => 10.0,
                'audio' => 15.0,
                'video' => 20.0,
                default => 5.0,
            };
        }
    }

    /**
     * Get workflow progress for a chat.
     *
     * @param Chat $chat
     * @return array
     */
    public function getProgress(Chat $chat): array
    {
        $totalSteps = $chat->service->workflowSteps()->count();
        $currentStep = $chat->current_step_order;
        $completedSteps = $currentStep - 1;

        return [
            'total_steps' => $totalSteps,
            'current_step' => $currentStep,
            'completed_steps' => $completedSteps,
            'progress_percentage' => $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0,
            'is_completed' => $chat->isCompleted(),
        ];
    }
}
