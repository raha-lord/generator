<?php

namespace App\Services\Chat;

use App\Models\Chat\Chat;
use App\Models\Chat\Message;

class ContextStorage
{
    /**
     * Get raw messages from chat for AI context.
     *
     * @param Chat $chat
     * @param int $limit Maximum number of messages to retrieve
     * @return array Array of messages with 'role' and 'content' keys
     */
    public function getMessages(Chat $chat, int $limit = 50): array
    {
        return Message::where('chat_id', $chat->id)
            ->where('status', 'completed')
            ->whereIn('role', ['user', 'assistant', 'system'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
                'created_at' => $msg->created_at,
            ])
            ->reverse()
            ->values()
            ->toArray();
    }

    /**
     * Store a new message in the chat.
     *
     * @param Chat $chat
     * @param string $role
     * @param string $content
     * @param array $metadata
     * @param int|null $workflowStepId
     * @param float $creditsSpent
     * @return Message
     */
    public function store(
        Chat $chat,
        string $role,
        string $content,
        array $metadata = [],
        ?int $workflowStepId = null,
        float $creditsSpent = 0
    ): Message {
        $type = match($role) {
            'user' => 'user_input',
            'assistant' => 'assistant_response',
            'system' => 'system',
            default => 'user_input',
        };

        return Message::create([
            'chat_id' => $chat->id,
            'workflow_step_id' => $workflowStepId,
            'type' => $type,
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata,
            'credits_spent' => $creditsSpent,
            'status' => 'completed',
        ]);
    }

    /**
     * Get the last N user messages.
     *
     * @param Chat $chat
     * @param int $count
     * @return array
     */
    public function getLastUserMessages(Chat $chat, int $count = 5): array
    {
        return Message::where('chat_id', $chat->id)
            ->where('role', 'user')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit($count)
            ->get()
            ->pluck('content')
            ->reverse()
            ->values()
            ->toArray();
    }

    /**
     * Get total message count for chat.
     *
     * @param Chat $chat
     * @return int
     */
    public function getMessageCount(Chat $chat): int
    {
        return Message::where('chat_id', $chat->id)->count();
    }

    /**
     * Clear all messages from chat (for reset).
     *
     * @param Chat $chat
     */
    public function clearMessages(Chat $chat): void
    {
        Message::where('chat_id', $chat->id)->delete();
    }
}
