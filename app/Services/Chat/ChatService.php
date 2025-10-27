<?php

namespace App\Services\Chat;

use App\Models\Chat\Chat;
use App\Models\Chat\Service;
use App\Models\User;
use Illuminate\Support\Str;

class ChatService
{
    /**
     * Create a new chat for a user.
     *
     * @param User $user
     * @param Service $service
     * @param string|null $title
     * @param array $metadata
     * @return Chat
     */
    public function create(User $user, Service $service, ?string $title = null, array $metadata = []): Chat
    {
        // Auto-generate title if not provided
        if (empty($title)) {
            $title = $this->generateTitle($service);
        }

        return Chat::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'service_id' => $service->id,
            'title' => $title,
            'status' => 'active',
            'current_step_order' => 1,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get user's active chats.
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserChats(User $user, int $limit = 20)
    {
        return Chat::where('user_id', $user->id)
            ->with('service')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's active chats only.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveChats(User $user)
    {
        return Chat::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('service')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Find chat by UUID.
     *
     * @param string $uuid
     * @return Chat|null
     */
    public function findByUuid(string $uuid): ?Chat
    {
        return Chat::where('uuid', $uuid)->with('service')->first();
    }

    /**
     * Update chat title.
     *
     * @param Chat $chat
     * @param string $title
     */
    public function updateTitle(Chat $chat, string $title): void
    {
        $chat->update(['title' => $title]);
    }

    /**
     * Archive a chat.
     *
     * @param Chat $chat
     */
    public function archive(Chat $chat): void
    {
        $chat->update(['status' => 'archived']);
    }

    /**
     * Delete a chat (soft delete messages, artifacts remain).
     *
     * @param Chat $chat
     */
    public function delete(Chat $chat): void
    {
        $chat->delete();
    }

    /**
     * Generate a title for a new chat.
     *
     * @param Service $service
     * @return string
     */
    private function generateTitle(Service $service): string
    {
        return $service->name . ' - ' . now()->format('M d, H:i');
    }

    /**
     * Update chat metadata.
     *
     * @param Chat $chat
     * @param array $metadata
     */
    public function updateMetadata(Chat $chat, array $metadata): void
    {
        $existingMetadata = $chat->metadata ?? [];
        $chat->update([
            'metadata' => array_merge($existingMetadata, $metadata)
        ]);
    }

    /**
     * Check if user owns the chat.
     *
     * @param Chat $chat
     * @param User $user
     * @return bool
     */
    public function userOwnsChat(Chat $chat, User $user): bool
    {
        return $chat->user_id === $user->id;
    }
}
