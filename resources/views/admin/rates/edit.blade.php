<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Редактировать курс конвертации') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.rates.update', $rate) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Provider -->
                            <div>
                                <label for="provider_id" class="block text-sm font-medium text-gray-700">Провайдер (опционально)</label>
                                <select id="provider_id" name="provider_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Общий курс (для всех провайдеров)</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->id }}" {{ old('provider_id', $rate->provider_id) == $provider->id ? 'selected' : '' }}>
                                            {{ $provider->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('provider_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- From Unit -->
                            <div>
                                <label for="from_unit" class="block text-sm font-medium text-gray-700">Из (единица измерения)</label>
                                <input type="text" id="from_unit" name="from_unit" value="{{ old('from_unit', $rate->from_unit) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('from_unit')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- To Currency -->
                            <div>
                                <label for="to_currency" class="block text-sm font-medium text-gray-700">В (валюта)</label>
                                <select id="to_currency" name="to_currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="RUB" {{ old('to_currency', $rate->to_currency) == 'RUB' ? 'selected' : '' }}>RUB (₽)</option>
                                    <option value="USD" {{ old('to_currency', $rate->to_currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="EUR" {{ old('to_currency', $rate->to_currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    <option value="credits" {{ old('to_currency', $rate->to_currency) == 'credits' ? 'selected' : '' }}>Credits</option>
                                </select>
                                @error('to_currency')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Rate -->
                            <div>
                                <label for="rate" class="block text-sm font-medium text-gray-700">Курс конвертации</label>
                                <input type="number" step="0.0001" id="rate" name="rate" value="{{ old('rate', $rate->rate) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <p class="mt-1 text-sm text-gray-500">Сколько стоит 1 единица источника</p>
                                @error('rate')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Markup Percentage -->
                            <div>
                                <label for="markup_percentage" class="block text-sm font-medium text-gray-700">Наценка (%)</label>
                                <input type="number" step="0.01" id="markup_percentage" name="markup_percentage" value="{{ old('markup_percentage', $rate->markup_percentage) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <p class="mt-1 text-sm text-gray-500">Процент наценки (0-100)</p>
                                @error('markup_percentage')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Validity Period -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="valid_from" class="block text-sm font-medium text-gray-700">Действителен с</label>
                                    <input type="datetime-local" id="valid_from" name="valid_from" value="{{ old('valid_from', $rate->valid_from?->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('valid_from')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="valid_until" class="block text-sm font-medium text-gray-700">Действителен до</label>
                                    <input type="datetime-local" id="valid_until" name="valid_until" value="{{ old('valid_until', $rate->valid_until?->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('valid_until')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Is Active -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $rate->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-gray-700">Активен</label>
                                    <p class="text-gray-500">Курс будет активен и использоваться для расчётов</p>
                                </div>
                            </div>

                            <!-- Current Calculation -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-blue-900">Текущий расчёт:</h4>
                                <p class="mt-2 text-sm text-blue-700">
                                    <strong>Базовый курс:</strong> <span id="example-base">1 {{ $rate->from_unit }} = {{ number_format($rate->rate, 2) }} {{ $rate->to_currency }}</span><br>
                                    <strong>Наценка:</strong> <span id="example-markup">{{ $rate->markup_percentage }}%</span><br>
                                    <strong>Итоговый курс:</strong> <span id="example-total">{{ number_format($rate->getConversionRate(), 2) }} {{ $rate->to_currency }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-x-4">
                            <a href="{{ route('admin.rates.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Отмена</a>
                            <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live calculation example
        const rateInput = document.getElementById('rate');
        const markupInput = document.getElementById('markup_percentage');
        const currencySelect = document.getElementById('to_currency');
        const unitInput = document.getElementById('from_unit');

        function updateExample() {
            const rate = parseFloat(rateInput.value) || 1;
            const markup = parseFloat(markupInput.value) || 0;
            const currency = currencySelect.value;
            const unit = unitInput.value || 'токен';
            const total = rate * (1 + markup / 100);

            document.getElementById('example-base').textContent = `1 ${unit} = ${rate.toFixed(2)} ${currency}`;
            document.getElementById('example-markup').textContent = `${markup}%`;
            document.getElementById('example-total').textContent = `${total.toFixed(2)} ${currency}`;
        }

        rateInput.addEventListener('input', updateExample);
        markupInput.addEventListener('input', updateExample);
        currencySelect.addEventListener('change', updateExample);
        unitInput.addEventListener('input', updateExample);
    </script>
</x-app-layout>
