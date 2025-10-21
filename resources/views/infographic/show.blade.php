<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Infographic Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <!-- Generation Info -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Generation Information</h3>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                <span class="ml-2 font-medium">
                                    @if($generation->status === 'completed')
                                        <span class="text-green-600 dark:text-green-400">✓ Completed</span>
                                    @elseif($generation->status === 'processing')
                                        <span class="text-yellow-600 dark:text-yellow-400">⏳ Processing</span>
                                    @elseif($generation->status === 'failed')
                                        <span class="text-red-600 dark:text-red-400">✗ Failed</span>
                                    @else
                                        <span class="text-gray-600 dark:text-gray-400">{{ ucfirst($generation->status) }}</span>
                                    @endif
                                </span>
                            </div>
                            
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Cost:</span>
                                <span class="ml-2 font-medium">{{ $generation->cost }} credits</span>
                            </div>
                            
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Created:</span>
                                <span class="ml-2 font-medium">{{ $generation->created_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Completed:</span>
                                <span class="ml-2 font-medium">
                                    {{ $generation->completed_at ? $generation->completed_at->format('Y-m-d H:i:s') : 'N/A' }}
                                </span>
                            </div>

                            <div class="col-span-2">
                                <span class="text-gray-600 dark:text-gray-400">UUID:</span>
                                <span class="ml-2 font-mono text-xs">{{ $generation->uuid }}</span>
                            </div>

                            <div class="col-span-2">
                                <span class="text-gray-600 dark:text-gray-400">Visibility:</span>
                                <span class="ml-2 font-medium">
                                    {{ $generation->is_public ? 'Public' : 'Private' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Original Prompt -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Original Prompt</h3>
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <p class="text-sm">{{ $generation->prompt }}</p>
                        </div>
                    </div>

                    <!-- Generated Content -->
                    @if($generation->status === 'completed' && $generation->result_path)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-3">Generated Infographic</h3>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                                @if($generation->generatable && $generation->generatable->image_path)
                                    <div class="flex justify-center">
                                        <img
                                            src="{{ Storage::disk('public')->url($generation->generatable->image_path) }}"
                                            alt="Generated Infographic"
                                            class="max-w-full h-auto rounded-lg shadow-lg"
                                        />
                                    </div>
                                    <div class="mt-4 text-center">
                                        <a
                                            href="{{ Storage::disk('public')->url($generation->generatable->image_path) }}"
                                            download
                                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Download Image
                                        </a>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Image not available</p>
                                @endif
                            </div>
                        </div>
                    @elseif($generation->status === 'processing')
                        <div class="mb-6">
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    Your infographic is being generated. Please refresh this page in a few moments.
                                </p>
                            </div>
                        </div>
                    @elseif($generation->status === 'failed')
                        <div class="mb-6">
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <p class="text-sm text-red-800 dark:text-red-200">
                                    Generation failed. Your credits have been refunded.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('history.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                            View All Generations
                        </a>

                        <div class="flex space-x-3">
                            @if($generation->user_id === auth()->id())
                                @if($generation->status === 'completed' && $generation->moderation_status === 'approved')
                                    <form method="POST" action="{{ route('history.togglePublic', $generation->uuid) }}">
                                        @csrf
                                        <button type="submit" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                            {{ $generation->is_public ? 'Make Private' : 'Make Public' }}
                                        </button>
                                    </form>
                                @endif

                                @if($generation->status === 'failed')
                                    <form method="POST" action="{{ route('history.retry', $generation->uuid) }}">
                                        @csrf
                                        <button type="submit" class="text-sm px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                            Retry Generation
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('history.destroy', $generation->uuid) }}" onsubmit="return confirm('Are you sure you want to delete this generation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('infographic.create') }}" class="text-sm px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Create New
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
