<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Редактировать прайсинг') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.pricing.update', $pricing) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Provider -->
                            <div>
                                <label for="provider_id" class="block text-sm font-medium text-gray-700">Провайдер</label>
                                <select id="provider_id" name="provider_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->id }}" {{ old('provider_id', $pricing->provider_id) == $provider->id ? 'selected' : '' }}>
                                            {{ $provider->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('provider_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Service Type -->
                            <div>
                                <label for="service_type" class="block text-sm font-medium text-gray-700">Тип сервиса</label>
                                <select id="service_type" name="service_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="image" {{ old('service_type', $pricing->service_type) == 'image' ? 'selected' : '' }}>Изображение</option>
                                    <option value="infographic" {{ old('service_type', $pricing->service_type) == 'infographic' ? 'selected' : '' }}>Инфографика</option>
                                    <option value="text" {{ old('service_type', $pricing->service_type) == 'text' ? 'selected' : '' }}>Текст</option>
                                </select>
                                @error('service_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Display Name -->
                            <div>
                                <label for="display_name" class="block text-sm font-medium text-gray-700">Название</label>
                                <input type="text" id="display_name" name="display_name" value="{{ old('display_name', $pricing->display_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('display_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Token Cost -->
                            <div>
                                <label for="token_cost" class="block text-sm font-medium text-gray-700">Стоимость (токены API)</label>
                                <input type="number" step="0.0001" id="token_cost" name="token_cost" value="{{ old('token_cost', $pricing->token_cost) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <p class="mt-1 text-sm text-gray-500">Количество токенов API, которое стоит эта операция</p>
                                @error('token_cost')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Conditions (JSON) -->
                            <div>
                                <label for="conditions" class="block text-sm font-medium text-gray-700">Условия (JSON)</label>
                                <textarea id="conditions" name="conditions" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('conditions', $pricing->conditions ? json_encode($pricing->conditions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                                <p class="mt-1 text-sm text-gray-500">Условия применения этого прайсинга (опционально)</p>
                                @error('conditions')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Порядок сортировки</label>
                                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $pricing->sort_order) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('sort_order')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Checkboxes -->
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="is_default" name="is_default" type="checkbox" value="1" {{ old('is_default', $pricing->is_default) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_default" class="font-medium text-gray-700">По умолчанию</label>
                                        <p class="text-gray-500">Использовать этот прайсинг по умолчанию для данного типа сервиса</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $pricing->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_active" class="font-medium text-gray-700">Активен</label>
                                        <p class="text-gray-500">Прайсинг будет активен и использоваться для расчётов</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Key (read-only) -->
                            <div>
                                <label for="pricing_key" class="block text-sm font-medium text-gray-700">Ключ прайсинга</label>
                                <input type="text" id="pricing_key" value="{{ $pricing->pricing_key }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm" readonly>
                                <p class="mt-1 text-sm text-gray-500">Уникальный идентификатор прайсинга (только для чтения)</p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-x-4">
                            <a href="{{ route('admin.pricing.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Отмена</a>
                            <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
