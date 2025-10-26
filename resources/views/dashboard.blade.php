<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Balance Card -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-2">Your Balance</h3>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                                {{ auth()->user()->balance->available_credits }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Available Credits
                            </p>
                        </div>
                        @if(auth()->user()->balance->reserved_credits > 0)
                            <div class="text-right">
                                <p class="text-xl font-semibold text-yellow-600 dark:text-yellow-400">
                                    {{ auth()->user()->balance->reserved_credits }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Reserved Credits
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

                <!-- Generate Infographic Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Generate Infographic</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Cost: 10 credits</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Create beautiful infographics powered by AI
                        </p>
                        <a
                            href="{{ route('infographic.create') }}"
                            class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition"
                        >
                            Start Generating
                        </a>
                    </div>
                </div>

                <!-- Generate AI Image Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Generate AI Image</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Cost: 5 credits</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Create stunning images with Pollinations.ai
                        </p>
                        <a
                            href="{{ route('image.create') }}"
                            class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition"
                        >
                            Start Generating
                        </a>
                    </div>
                </div>

                <!-- View History Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Generation History</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">View all generations</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Access and manage your previous generations
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
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Welcome to AI Content Generator!</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Get started by generating your first infographic or check out your generation history.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
