<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Balance Card -->
            <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">Your Balance</h3>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-bold text-indigo-600">
                                {{ auth()->user()->balance->available_credits }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Available Credits
                            </p>
                        </div>
                        @if(auth()->user()->balance->reserved_credits > 0)
                            <div class="text-right">
                                <p class="text-xl font-semibold text-yellow-600">
                                    {{ auth()->user()->balance->reserved_credits }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Reserved Credits
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                <!-- Chats Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">My Chats</h3>
                                <p class="text-sm text-gray-600">AI Conversations</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Start a new conversation or continue existing chats
                        </p>
                        <a
                            href="{{ route('chats.index') }}"
                            class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                        >
                            Go to Chats
                        </a>
                    </div>
                </div>

                <!-- Chat History Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Chat History</h3>
                                <p class="text-sm text-gray-600">View past chats</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Access and manage your previous conversations
                        </p>
                        <a
                            href="{{ route('history.index') }}"
                            class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                        >
                            View History
                        </a>
                    </div>
                </div>

            </div>

            <!-- Recent Activity -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome to AI Chat Platform!</h3>
                    <p class="text-gray-600">
                        Start a new conversation with AI or review your chat history.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
