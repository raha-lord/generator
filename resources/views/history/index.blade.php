<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($chats->isEmpty())
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No chat history yet</h3>
                    <p class="mt-2 text-gray-500">Start your first conversation!</p>
                    <div class="mt-6">
                        <a href="{{ route('chats.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Create your first chat
                        </a>
                    </div>
                </div>
            @else
                <!-- Chats List -->
                <div class="space-y-4">
                    @foreach($chats as $chat)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 hover:shadow-md transition">
                            <a href="{{ route('chats.show', $chat->uuid) }}" class="block p-6">
                                <!-- Chat Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3 flex-1">
                                        <span class="text-3xl">{{ $chat->service->icon }}</span>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $chat->title }}
                                            </h3>
                                            <div class="flex items-center space-x-3 text-sm text-gray-500 mt-1">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    {{ $chat->service->name }}
                                                </span>
                                                @if($chat->service->ai_model)
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                        </svg>
                                                        {{ $chat->service->ai_model }}
                                                    </span>
                                                @endif
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                    </svg>
                                                    {{ ucfirst($chat->service->type) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end space-y-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $chat->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $chat->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $chat->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($chat->status) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $chat->updated_at->format('M d, Y') }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $chat->updated_at->format('H:i') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Message Preview -->
                                @if($chat->messages->count() > 0)
                                    <div class="space-y-3 mt-4 pt-4 border-t border-gray-100">
                                        @foreach($chat->messages->take(3) as $message)
                                            <div class="flex items-start space-x-2">
                                                <div class="flex-shrink-0">
                                                    @if($message->role === 'user')
                                                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        </div>
                                                    @else
                                                        <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                                                            <svg class="w-3 h-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                                                                <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs text-gray-500 mb-1">
                                                        {{ $message->role === 'user' ? 'You' : 'AI' }}
                                                        <span class="text-gray-400">• {{ $message->created_at->diffForHumans() }}</span>
                                                    </p>
                                                    <p class="text-sm text-gray-700 line-clamp-2">
                                                        {{ Str::limit($message->content, 150) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach

                                        @if($chat->messages->count() > 3)
                                            <p class="text-xs text-gray-400 italic pt-2">
                                                + {{ $chat->messages->count() - 3 }} more messages
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400 italic mt-4 pt-4 border-t border-gray-100">
                                        No messages yet
                                    </p>
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($chats->hasPages())
                    <div class="mt-6">
                        {{ $chats->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
