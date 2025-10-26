<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Generate Infographic') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 ">
                    
                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('info') }}</span>
                        </div>
                    @endif

                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Available credits: <strong>{{ auth()->user()->balance->available_credits }}</strong>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Estimated cost: <strong id="estimated-cost">~10 credits</strong>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Cost = Slides × Cost per slide (varies by provider and resolution)
                        </p>
                    </div>

                    <form method="POST" action="{{ route('infographic.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="prompt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Infographic Topic or Description
                            </label>
                            <textarea 
                                name="prompt" 
                                id="prompt" 
                                rows="5"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500  "
                                placeholder="Describe the infographic you want to create. For example: 'Create an infographic about the benefits of renewable energy' or 'Statistics on global warming trends'"
                                required
                                minlength="10"
                                maxlength="1000"
                            >{{ old('prompt', session('prompt')) }}</textarea>
                            
                            @error('prompt')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Enter between 10 and 1000 characters
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="provider_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    AI Provider
                                </label>
                                <select
                                    name="provider_id"
                                    id="provider_id"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->id }}" {{ old('provider_id', 2) == $provider->id ? 'selected' : '' }}>
                                            {{ $provider->display_name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('provider_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="slides_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Number of Slides
                                </label>
                                <input
                                    type="number"
                                    name="slides_count"
                                    id="slides_count"
                                    min="1"
                                    max="10"
                                    value="{{ old('slides_count', 1) }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                />

                                @error('slides_count')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Generate 1-10 slides for your infographic
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="width" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Width (px)
                                </label>
                                <input
                                    type="number"
                                    name="width"
                                    id="width"
                                    min="256"
                                    max="4096"
                                    step="64"
                                    value="{{ old('width', 1024) }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                />

                                @error('width')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="height" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Height (px)
                                </label>
                                <input
                                    type="number"
                                    name="height"
                                    id="height"
                                    min="256"
                                    max="4096"
                                    step="64"
                                    value="{{ old('height', 1024) }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                />

                                @error('height')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="style" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Visual Style (Optional)
                            </label>
                            <select 
                                name="style" 
                                id="style"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500  "
                            >
                                <option value="">Default</option>
                                <option value="modern" {{ old('style') === 'modern' ? 'selected' : '' }}>Modern</option>
                                <option value="classic" {{ old('style') === 'classic' ? 'selected' : '' }}>Classic</option>
                                <option value="minimalist" {{ old('style') === 'minimalist' ? 'selected' : '' }}>Minimalist</option>
                                <option value="colorful" {{ old('style') === 'colorful' ? 'selected' : '' }}>Colorful</option>
                                <option value="professional" {{ old('style') === 'professional' ? 'selected' : '' }}>Professional</option>
                            </select>
                            
                            @error('style')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="format" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Output Format (Optional)
                            </label>
                            <select 
                                name="format" 
                                id="format"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500  "
                            >
                                <option value="">Default (PNG)</option>
                                <option value="png" {{ old('format') === 'png' ? 'selected' : '' }}>PNG</option>
                                <option value="jpg" {{ old('format') === 'jpg' ? 'selected' : '' }}>JPG</option>
                                <option value="svg" {{ old('format') === 'svg' ? 'selected' : '' }}>SVG</option>
                            </select>
                            
                            @error('format')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                Back to Dashboard
                            </a>
                            
                            <button 
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Generate Infographic
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Simple cost estimation (you can make this more sophisticated with AJAX)
        function updateEstimatedCost() {
            const slidesCount = parseInt(document.getElementById('slides_count').value) || 1;
            const width = parseInt(document.getElementById('width').value) || 1024;
            const height = parseInt(document.getElementById('height').value) || 1024;

            // Simple estimation: base cost increases with resolution
            const pixels = width * height;
            let baseCost = 5; // Default

            if (pixels > 2000000) { // > 2MP
                baseCost = 20;
            } else if (pixels > 1000000) { // > 1MP
                baseCost = 10;
            } else if (pixels > 500000) { // > 0.5MP
                baseCost = 8;
            }

            const totalCost = slidesCount * baseCost;
            document.getElementById('estimated-cost').textContent = `~${totalCost} credits`;
        }

        // Update cost when inputs change
        document.getElementById('slides_count').addEventListener('input', updateEstimatedCost);
        document.getElementById('width').addEventListener('input', updateEstimatedCost);
        document.getElementById('height').addEventListener('input', updateEstimatedCost);
        document.getElementById('provider_id').addEventListener('change', updateEstimatedCost);

        // Initial calculation
        updateEstimatedCost();
    </script>
    @endpush
</x-app-layout>
