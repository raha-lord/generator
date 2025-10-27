<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat\Chat;
use App\Models\Chat\Service;
use App\Services\Chat\ChatService;
use App\Services\Chat\MessageService;
use App\Services\Chat\WorkflowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService,
        private MessageService $messageService,
        private WorkflowEngine $workflowEngine
    ) {}

    /**
     * Get user's chats.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $chats = $this->chatService->getUserChats($request->user());

        return response()->json([
            'success' => true,
            'chats' => $chats->map(fn($chat) => [
                'uuid' => $chat->uuid,
                'title' => $chat->title,
                'service' => [
                    'code' => $chat->service->code,
                    'name' => $chat->service->name,
                    'icon' => $chat->service->icon,
                    'color' => $chat->service->color,
                ],
                'status' => $chat->status,
                'created_at' => $chat->created_at->toISOString(),
                'updated_at' => $chat->updated_at->toISOString(),
            ]),
        ]);
    }

    /**
     * Create a new chat.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_code' => 'required|string|exists:chat.services,code',
            'title' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        $service = Service::where('code', $validated['service_code'])
            ->where('is_active', true)
            ->firstOrFail();

        $chat = $this->chatService->create(
            user: $request->user(),
            service: $service,
            title: $validated['title'] ?? null,
            metadata: $validated['metadata'] ?? []
        );

        // Get first step info
        $currentStep = $chat->getCurrentStep();

        return response()->json([
            'success' => true,
            'chat' => [
                'uuid' => $chat->uuid,
                'title' => $chat->title,
                'service' => [
                    'code' => $service->code,
                    'name' => $service->name,
                    'type' => $service->type,
                ],
                'status' => $chat->status,
                'current_step' => $currentStep ? [
                    'order' => $currentStep->order,
                    'name' => $currentStep->name,
                    'model_type' => $currentStep->model_type,
                    'requires_confirmation' => $currentStep->requires_confirmation,
                ] : null,
                'progress' => $this->workflowEngine->getProgress($chat),
            ],
        ], 201);
    }

    /**
     * Get chat details with messages.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function show(string $uuid, Request $request): JsonResponse
    {
        $chat = Chat::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if (!$this->chatService->userOwnsChat($chat, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $messages = $this->messageService->getChatMessages($chat);
        $currentStep = $chat->getCurrentStep();

        return response()->json([
            'success' => true,
            'chat' => [
                'uuid' => $chat->uuid,
                'title' => $chat->title,
                'service' => [
                    'code' => $chat->service->code,
                    'name' => $chat->service->name,
                    'type' => $chat->service->type,
                ],
                'status' => $chat->status,
                'current_step' => $currentStep ? [
                    'order' => $currentStep->order,
                    'name' => $currentStep->name,
                    'model_type' => $currentStep->model_type,
                    'requires_confirmation' => $currentStep->requires_confirmation,
                ] : null,
                'progress' => $this->workflowEngine->getProgress($chat),
                'metadata' => $chat->metadata,
            ],
            'messages' => $messages->map(fn($msg) => [
                'id' => $msg->id,
                'type' => $msg->type,
                'role' => $msg->role,
                'content' => $msg->content,
                'metadata' => $msg->metadata,
                'credits_spent' => (float) $msg->credits_spent,
                'status' => $msg->status,
                'workflow_step' => $msg->workflowStep ? [
                    'name' => $msg->workflowStep->name,
                    'order' => $msg->workflowStep->order,
                ] : null,
                'created_at' => $msg->created_at->toISOString(),
            ]),
        ]);
    }

    /**
     * Send a message to chat (execute current workflow step).
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(string $uuid, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        $chat = Chat::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if (!$this->chatService->userOwnsChat($chat, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if chat is active
        if (!$chat->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Chat is not active',
            ], 400);
        }

        try {
            // Execute current workflow step
            $result = $this->workflowEngine->executeStep($chat, $validated['message']);

            $response = [
                'success' => true,
                'message' => [
                    'id' => $result['message']->id,
                    'role' => $result['message']->role,
                    'content' => $result['message']->content,
                    'metadata' => $result['message']->metadata,
                    'credits_spent' => (float) $result['credits_spent'],
                    'created_at' => $result['message']->created_at->toISOString(),
                ],
                'step' => [
                    'order' => $result['step']->order,
                    'name' => $result['step']->name,
                    'model_type' => $result['step']->model_type,
                ],
            ];

            // Check if step requires confirmation before moving to next
            if ($result['step']->requiresConfirmation()) {
                $response['requires_confirmation'] = true;
                $response['message_text'] = 'Please confirm to continue to the next step';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Failed to send message', [
                'chat_uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Continue to next workflow step.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function continueWorkflow(string $uuid, Request $request): JsonResponse
    {
        $chat = Chat::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if (!$this->chatService->userOwnsChat($chat, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $hasNextStep = $this->workflowEngine->moveToNextStep($chat);

        $chat->refresh();
        $currentStep = $chat->getCurrentStep();

        return response()->json([
            'success' => true,
            'has_next_step' => $hasNextStep,
            'chat_status' => $chat->status,
            'current_step' => $currentStep ? [
                'order' => $currentStep->order,
                'name' => $currentStep->name,
                'model_type' => $currentStep->model_type,
                'requires_confirmation' => $currentStep->requires_confirmation,
            ] : null,
            'progress' => $this->workflowEngine->getProgress($chat),
        ]);
    }

    /**
     * Delete a chat.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(string $uuid, Request $request): JsonResponse
    {
        $chat = Chat::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if (!$this->chatService->userOwnsChat($chat, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->chatService->delete($chat);

        return response()->json([
            'success' => true,
            'message' => 'Chat deleted successfully',
        ]);
    }

    /**
     * Update chat title.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function updateTitle(string $uuid, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $chat = Chat::where('uuid', $uuid)->firstOrFail();

        // Check ownership
        if (!$this->chatService->userOwnsChat($chat, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->chatService->updateTitle($chat, $validated['title']);

        return response()->json([
            'success' => true,
            'title' => $validated['title'],
        ]);
    }
}
