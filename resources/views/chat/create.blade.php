<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Choose a service</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($services as $service)
                        <div x-data="{ creating: false }">
                            <button @click="createChat('{{ $service->code }}')"
                                    :disabled="creating"
                                    class="w-full text-left bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <span class="text-4xl">{{ $service->icon }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-semibold text-gray-900">{{ $service->name }}</h4>
                                        <p class="mt-1 text-sm text-gray-600">{{ $service->description }}</p>
                                        <div class="mt-3 flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst($service->type) }}
                                            </span>
                                            @if($service->type === 'multi_step')
                                                <span class="text-xs text-gray-500">
                                                    {{ $service->workflowSteps->count() }} steps
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div x-show="creating" class="mt-4 flex items-center justify-center">
                                    <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                                    <span class="ml-2 text-sm text-gray-600">Creating...</span>
                                </div>
                            </button>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-12">
                            <p class="text-gray-500">No services available. Please contact administrator.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        async function createChat(serviceCode) {
            const creating = true;

            try {
                const response = await fetch('/api/chats', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        service_code: serviceCode
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = `/chats/${data.chat.uuid}`;
                } else {
                    alert('Failed to create chat: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Failed to create chat:', error);
                alert('Failed to create chat. Please try again.');
            }
        }
    </script>
</x-app-layout>
