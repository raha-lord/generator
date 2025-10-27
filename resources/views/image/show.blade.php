<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AI Image Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900">

                    <!-- Generation Info -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Generation Information</h3>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Status:</span>
                                <span class="ml-2 font-medium">
                                    @if($generation->status === 'completed')
                                        <span class="text-green-600">✓ Completed</span>
                                    @elseif($generation->status === 'processing')
                                        <span class="text-yellow-600">⏳ Processing</span>
                                    @elseif($generation->status === 'failed')
                                        <span class="text-red-600">✗ Failed</span>
                                    @else
                                        <span class="text-gray-600">{{ ucfirst($generation->status) }}</span>
                                    @endif
                                </span>
                            </div>

                            <div>
                                <span class="text-gray-600">Cost:</span>
                                <span class="ml-2 font-medium">{{ $generation->cost }} credits</span>
                            </div>

                            <div>
                                <span class="text-gray-600">Created:</span>
                                <span class="ml-2 font-medium">{{ $generation->created_at->format('Y-m-d H:i:s') }}</span>
                            </div>

                            <div>
                                <span class="text-gray-600">Completed:</span>
                                <span class="ml-2 font-medium">
                                    {{ $generation->completed_at ? $generation->completed_at->format('Y-m-d H:i:s') : 'N/A' }}
                                </span>
                            </div>

                            @if($generation->generatable)
                                <div>
                                    <span class="text-gray-600">Dimensions:</span>
                                    <span class="ml-2 font-medium">{{ $generation->generatable->width }} x {{ $generation->generatable->height }}px</span>
                                </div>

                                <div>
                                    <span class="text-gray-600">AI Model:</span>
                                    <span class="ml-2 font-medium">{{ $generation->generatable->model_display_name }}</span>
                                </div>

                                @if($generation->generatable->enhanced)
                                    <div>
                                        <span class="text-gray-600">Enhanced:</span>
                                        <span class="ml-2 font-medium text-green-600">Yes</span>
                                    </div>
                                @endif

                                @if($generation->generatable->seed)
                                    <div>
                                        <span class="text-gray-600">Seed:</span>
                                        <span class="ml-2 font-mono text-xs">{{ $generation->generatable->seed }}</span>
                                    </div>
                                @endif
                            @endif

                            <div class="col-span-2">
                                <span class="text-gray-600">UUID:</span>
                                <span class="ml-2 font-mono text-xs">{{ $generation->uuid }}</span>
                            </div>

                            <div class="col-span-2">
                                <span class="text-gray-600">Visibility:</span>
                                <span class="ml-2 font-medium">
                                    {{ $generation->is_public ? 'Public' : 'Private' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Original Prompt -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Original Prompt</h3>
                        <div class="prompt-box">
                            <p class="text-sm">{{ $generation->prompt }}</p>
                        </div>
                    </div>

                    <!-- Generated Content -->
                    @if($generation->status === 'completed' && $generation->result_path)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-3">Generated Image</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                @if($generation->generatable && $generation->generatable->image_path)
                                    <div class="flex justify-center">
                                        <img
                                            src="{{ $generation->generatable->image_url }}"
                                            alt="Generated Image"
                                            class="max-w-full h-auto rounded-lg shadow-lg"
                                        />
                                    </div>
                                    <div class="mt-4 text-center">
                                        <a
                                            href="{{ $generation->generatable->download_url }}"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Download Image
                                        </a>
                                    </div>

                                    @if($generation->generatable->file_size)
                                        <div class="mt-2 text-center text-xs text-gray-500">
                                            File size: {{ $generation->generatable->human_file_size }}
                                        </div>
                                    @endif
                                @else
                                    <p class="text-sm text-gray-500">Image not available</p>
                                @endif
                            </div>
                        </div>
                    @elseif($generation->status === 'processing')
                        <div class="mb-6">
                            <div class="info-box">
                                <p class="text-sm">
                                    Your image is being generated. Please refresh this page in a few moments.
                                </p>
                            </div>
                        </div>
                    @elseif($generation->status === 'failed')
                        <div class="mb-6">
                            <div class="error-box">
                                <p class="text-sm">
                                    Generation failed. Your credits have been refunded.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Powered by notice -->
                    <div class="mb-6 text-center">
                        <p class="text-xs text-gray-500">
                            Powered by <a href="https://pollinations.ai" target="_blank" class="underline hover:text-gray-700">Pollinations.ai</a>
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('history.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
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

                            <a href="{{ route('image.create') }}" class="text-sm px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Create New
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
