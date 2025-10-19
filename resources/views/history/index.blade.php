<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Generation History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <!-- Filters -->
                    <div class="mb-6">
                        <form method="GET" action="{{ route('history.index') }}" class="flex flex-wrap gap-4">
                            
                            <div class="flex-1 min-w-[200px]">
                                <input 
                                    type="text" 
                                    name="search" 
                                    value="{{ $filters['search'] }}"
                                    placeholder="Search by prompt..."
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                                >
                            </div>

                            <div>
                                <select 
                                    name="status"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>All Status</option>
                                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $filters['status'] === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="failed" {{ $filters['status'] === 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>

                            <div>
                                <select 
                                    name="type"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                                >
                                    <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }}>All Types</option>
                                    <option value="infographic" {{ $filters['type'] === 'infographic' ? 'selected' : '' }}>Infographic</option>
                                </select>
                            </div>

                            <button 
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                Filter
                            </button>

                            @if($filters['search'] || $filters['status'] !== 'all' || $filters['type'] !== 'all')
                                <a 
                                    href="{{ route('history.index') }}"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                                >
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Generations List -->
                    @if($generations->count() > 0)
                        <div class="space-y-4">
                            @foreach($generations as $generation)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h3 class="font-semibold">
                                                    {{ class_basename($generation->generatable_type) }}
                                                </h3>
                                                
                                                <span class="text-xs px-2 py-1 rounded 
                                                    {{ $generation->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                                    {{ $generation->status === 'processing' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                    {{ $generation->status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                                    {{ $generation->status === 'pending' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : '' }}
                                                ">
                                                    {{ ucfirst($generation->status) }}
                                                </span>

                                                @if($generation->is_public)
                                                    <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        Public
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                {{ Str::limit($generation->prompt, 150) }}
                                            </p>

                                            <div class="flex gap-4 text-xs text-gray-500 dark:text-gray-400">
                                                <span>Cost: {{ $generation->cost }} credits</span>
                                                <span>•</span>
                                                <span>Created: {{ $generation->created_at->diffForHumans() }}</span>
                                                @if($generation->completed_at)
                                                    <span>•</span>
                                                    <span>Completed: {{ $generation->completed_at->diffForHumans() }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-2 ml-4">
                                            <a 
                                                href="{{ route('history.show', $generation->uuid) }}"
                                                class="text-sm px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-center"
                                            >
                                                View
                                            </a>

                                            @if($generation->status === 'failed')
                                                <form method="POST" action="{{ route('history.retry', $generation->uuid) }}">
                                                    @csrf
                                                    <button 
                                                        type="submit"
                                                        class="text-sm px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 w-full"
                                                    >
                                                        Retry
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('history.destroy', $generation->uuid) }}" onsubmit="return confirm('Delete this generation?');">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit"
                                                    class="text-sm px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 w-full"
                                                >
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $generations->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 dark:text-gray-400 mb-4">No generations found.</p>
                            <a 
                                href="{{ route('infographic.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                            >
                                Create Your First Infographic
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
