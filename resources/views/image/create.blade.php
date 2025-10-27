<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate AI Image') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900">

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

                    <div class="mb-6">
                        <p class="text-sm text-gray-600">
                            Available credits: <strong>{{ auth()->user()->balance->available_credits }}</strong>
                        </p>
                        <p class="text-sm text-gray-600">
                            Cost per generation: <strong>5 credits</strong>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Powered by Pollinations.ai - Free AI image generation
                        </p>
                    </div>

                    <form method="POST" action="{{ route('image.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="prompt" class="block text-sm font-medium text-gray-700 mb-2">
                                Image Description
                            </label>
                            <textarea
                                name="prompt"
                                id="prompt"
                                rows="5"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-900"
                                placeholder="Describe the image you want to generate. For example: 'A beautiful sunset over the ocean with palm trees' or 'Futuristic cityscape with flying cars at night'"
                                required
                                minlength="10"
                                maxlength="1000"
                            >{{ old('prompt', session('prompt')) }}</textarea>

                            @error('prompt')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <p class="mt-1 text-xs text-gray-500">
                                Enter between 10 and 1000 characters
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="width" class="block text-sm font-medium text-gray-700 mb-2">
                                    Width
                                </label>
                                <select
                                    name="width"
                                    id="width"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-900"
                                >
                                    <option value="512" {{ old('width') == '512' ? 'selected' : '' }}>512px</option>
                                    <option value="768" {{ old('width') == '768' ? 'selected' : '' }}>768px</option>
                                    <option value="1024" {{ old('width', '1024') == '1024' ? 'selected' : '' }}>1024px (Default)</option>
                                    <option value="1536" {{ old('width') == '1536' ? 'selected' : '' }}>1536px</option>
                                    <option value="2048" {{ old('width') == '2048' ? 'selected' : '' }}>2048px</option>
                                </select>

                                @error('width')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="height" class="block text-sm font-medium text-gray-700 mb-2">
                                    Height
                                </label>
                                <select
                                    name="height"
                                    id="height"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-900"
                                >
                                    <option value="512" {{ old('height') == '512' ? 'selected' : '' }}>512px</option>
                                    <option value="768" {{ old('height') == '768' ? 'selected' : '' }}>768px</option>
                                    <option value="1024" {{ old('height', '1024') == '1024' ? 'selected' : '' }}>1024px (Default)</option>
                                    <option value="1536" {{ old('height') == '1536' ? 'selected' : '' }}>1536px</option>
                                    <option value="2048" {{ old('height') == '2048' ? 'selected' : '' }}>2048px</option>
                                </select>

                                @error('height')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="model" class="block text-sm font-medium text-gray-700 mb-2">
                                AI Model
                            </label>
                            <select
                                name="model"
                                id="model"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-white text-gray-900"
                            >
                                <option value="flux" {{ old('model', 'flux') === 'flux' ? 'selected' : '' }}>Flux (Default - Balanced)</option>
                                <option value="flux-realism" {{ old('model') === 'flux-realism' ? 'selected' : '' }}>Flux Realism (Photorealistic)</option>
                                <option value="turbo" {{ old('model') === 'turbo' ? 'selected' : '' }}>Turbo (Fast)</option>
                            </select>

                            @error('model')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <p class="mt-1 text-xs text-gray-500">
                                Choose the AI model for generation
                            </p>
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="enhance"
                                    value="1"
                                    {{ old('enhance') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-600">
                                    Enhance quality (may take longer)
                                </span>
                            </label>

                            @error('enhance')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                Back to Dashboard
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Generate Image
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
