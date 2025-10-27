<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Chats') }}
            </h2>
            <a href="{{ route('chats.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Chat
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div x-data="chatList()">
                <!-- Loading State -->
                <div x-show="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-gray-600">Loading chats...</p>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && chats.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No chats yet</h3>
                    <p class="mt-2 text-gray-500">Get started by creating a new chat!</p>
                    <div class="mt-6">
                        <a href="{{ route('chats.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Create your first chat
                        </a>
                    </div>
                </div>

                <!-- Chats Grid -->
                <div x-show="!loading && chats.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="chat in chats" :key="chat.uuid">
                        <a :href="`/chats/${chat.uuid}`" class="block">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200 cursor-pointer">
                                <div class="p-6">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center space-x-3 flex-1">
                                            <span class="text-3xl" x-text="chat.service.icon"></span>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-lg font-semibold text-gray-900 truncate" x-text="chat.title"></h3>
                                                <p class="text-sm text-gray-500" x-text="chat.service.name"></p>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-green-100 text-green-800': chat.status === 'active',
                                                  'bg-blue-100 text-blue-800': chat.status === 'completed',
                                                  'bg-gray-100 text-gray-800': chat.status === 'archived'
                                              }"
                                              x-text="chat.status">
                                        </span>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                                        <span x-text="formatDate(chat.updated_at)"></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function chatList() {
            return {
                chats: [],
                loading: true,

                init() {
                    this.fetchChats();
                },

                async fetchChats() {
                    try {
                        const response = await fetch('/api/chats', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        this.chats = data.chats;
                    } catch (error) {
                        console.error('Failed to fetch chats:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const diff = now - date;
                    const hours = Math.floor(diff / 1000 / 60 / 60);

                    if (hours < 24) {
                        return hours === 0 ? 'Just now' : `${hours}h ago`;
                    }

                    const days = Math.floor(hours / 24);
                    if (days < 7) {
                        return `${days}d ago`;
                    }

                    return date.toLocaleDateString();
                }
            }
        }
    </script>
</x-app-layout>
