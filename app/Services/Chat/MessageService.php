<?php

namespace App\Services\Chat;

use App\Models\Chat\Chat;
use App\Models\Chat\Message;
use App\Models\Chat\WorkflowStep;

class MessageService
{
    public function __construct(
        private ContextStorage $contextStorage
    ) {}

    /**
     * Create a user message.
     *
     * @param Chat $chat
     * @param string $content
     * @param WorkflowStep|null $step
     * @return Message
     */
    public function createUserMessage(Chat $chat, string $content, ?WorkflowStep $step = null): Message
    {
        return $this->contextStorage->store(
            chat: $chat,
            role: 'user',
            content: $content,
            metadata: [],
            workflowStepId: $step?->id
        );
    }

    /**
     * Create an assistant message.
     *
     * @param Chat $chat
     * @param string $content
     * @param array $metadata
     * @param WorkflowStep|null $step
     * @param float $creditsSpent
     * @return Message
     */
    public function createAssistantMessage(
        Chat $chat,
        string $content,
        array $metadata = [],
        ?WorkflowStep $step = null,
        float $creditsSpent = 0
    ): Message {
        return $this->contextStorage->store(
            chat: $chat,
            role: 'assistant',
            content: $content,
            metadata: $metadata,
            workflowStepId: $step?->id,
            creditsSpent: $creditsSpent
        );
    }

    /**
     * Create a system message.
     *
     * @param Chat $chat
     * @param string $content
     * @return Message
     */
    public function createSystemMessage(Chat $chat, string $content): Message
    {
        return $this->contextStorage->store(
            chat: $chat,
            role: 'system',
            content: $content
        );
    }

    /**
     * Create a pending message (for async processing).
     *
     * @param Chat $chat
     * @param string $role
     * @param string $content
     * @param WorkflowStep|null $step
     * @return Message
     */
    public function createPendingMessage(Chat $chat, string $role, string $content, ?WorkflowStep $step = null): Message
    {
        $type = $role === 'user' ? 'user_input' : 'assistant_response';

        return Message::create([
            'chat_id' => $chat->id,
            'workflow_step_id' => $step?->id,
            'type' => $type,
            'role' => $role,
            'content' => $content,
            'status' => 'pending',
        ]);
    }

    /**
     * Mark message as completed.
     *
     * @param Message $message
     * @param string|null $content
     * @param array $metadata
     * @param float $creditsSpent
     */
    public function markAsCompleted(
        Message $message,
        ?string $content = null,
        array $metadata = [],
        float $creditsSpent = 0
    ): void {
        $updates = [
            'status' => 'completed',
            'credits_spent' => $creditsSpent,
        ];

        if ($content !== null) {
            $updates['content'] = $content;
        }

        if (!empty($metadata)) {
            $updates['metadata'] = array_merge($message->metadata ?? [], $metadata);
        }

        $message->update($updates);
    }

    /**
     * Mark message as failed.
     *
     * @param Message $message
     * @param string $error
     */
    public function markAsFailed(Message $message, string $error): void
    {
        $message->update([
            'status' => 'failed',
            'metadata' => array_merge($message->metadata ?? [], [
                'error' => $error,
                'failed_at' => now()->toISOString(),
            ]),
        ]);
    }

    /**
     * Get all messages for a chat.
     *
     * @param Chat $chat
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChatMessages(Chat $chat)
    {
        return $chat->messages()->with('workflowStep')->get();
    }
}
