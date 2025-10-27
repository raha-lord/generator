<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route('chats.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight" x-data x-text="$store.chat.chat?.title || 'Loading...'">
                        Loading...
                    </h2>
                    <p class="text-sm text-gray-500" x-data x-text="$store.chat.chat?.service.name"></p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="h-[calc(100vh-8rem)]">
        <div class="max-w-5xl mx-auto h-full sm:px-6 lg:px-8 py-6">
            <div class="bg-white shadow-sm sm:rounded-lg h-full flex flex-col" x-data="chatInterface('{{ $chatUuid }}')">

                <!-- Progress Bar (for multi-step services) -->
                <div x-show="chat && chat.service.type === 'multi_step'" class="p-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Progress</span>
                        <span class="text-sm text-gray-500" x-text="`Step ${progress.current_step} of ${progress.total_steps}`"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                             :style="`width: ${progress.progress_percentage}%`"></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-600" x-text="currentStep?.name"></p>
                </div>

                <!-- Messages Container -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4" x-ref="messagesContainer">
                    <!-- Loading State -->
                    <div x-show="loading && messages.length === 0" class="flex justify-center items-center h-full">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                            <p class="mt-4 text-gray-600">Loading chat...</p>
                        </div>
                    </div>

                    <!-- Messages -->
                    <template x-for="message in messages" :key="message.id">
                        <div class="flex" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-3xl"
                                 :class="message.role === 'user' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900'"
                                 class="rounded-lg px-4 py-3">
                                <div class="flex items-start space-x-2">
                                    <div class="flex-1">
                                        <p class="text-sm whitespace-pre-wrap" x-text="message.content"></p>
                                        <div class="mt-2 flex items-center justify-between text-xs opacity-75">
                                            <span x-text="formatTime(message.created_at)"></span>
                                            <span x-show="message.credits_spent > 0" class="ml-3">
                                                💎 <span x-text="message.credits_spent"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Confirmation Button (for multi-step) -->
                    <div x-show="waitingForConfirmation" class="flex justify-center">
                        <button @click="continueWorkflow()"
                                :disabled="sending"
                                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!sending">Continue to Next Step →</span>
                            <span x-show="sending">Processing...</span>
                        </button>
                    </div>

                    <!-- Sending Indicator -->
                    <div x-show="sending" class="flex justify-start">
                        <div class="bg-gray-100 rounded-lg px-4 py-3">
                            <div class="flex items-center space-x-2">
                                <div class="flex space-x-1">
                                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                                </div>
                                <span class="text-sm text-gray-600">AI is thinking...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="border-t border-gray-200 p-4">
                    <form @submit.prevent="sendMessage()" class="flex space-x-4">
                        <input type="text"
                               x-model="input"
                               :disabled="sending || chat?.status !== 'active'"
                               placeholder="Type your message..."
                               class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:bg-gray-100 disabled:cursor-not-allowed">
                        <button type="submit"
                                :disabled="!input.trim() || sending || chat?.status !== 'active'"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </form>
                    <p class="mt-2 text-xs text-gray-500" x-show="chat?.status !== 'active'">
                        This chat is <span x-text="chat?.status"></span> and cannot accept new messages.
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Global Alpine store for chat data
        document.addEventListener('alpine:init', () => {
            Alpine.store('chat', {
                chat: null,
            });
        });

        function chatInterface(chatUuid) {
            return {
                chatUuid: chatUuid,
                chat: null,
                messages: [],
                progress: {},
                currentStep: null,
                input: '',
                loading: true,
                sending: false,
                waitingForConfirmation: false,

                init() {
                    this.loadChat();
                },

                async loadChat() {
                    try {
                        const response = await fetch(`/api/chats/${this.chatUuid}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.chat = data.chat;
                            this.messages = data.messages;
                            this.progress = this.chat.progress;
                            this.currentStep = this.chat.current_step;

                            // Update global store
                            Alpine.store('chat').chat = this.chat;

                            this.$nextTick(() => this.scrollToBottom());
                        }
                    } catch (error) {
                        console.error('Failed to load chat:', error);
                        alert('Failed to load chat');
                    } finally {
                        this.loading = false;
                    }
                },

                async sendMessage() {
                    if (!this.input.trim() || this.sending) return;

                    const message = this.input;
                    this.input = '';
                    this.sending = true;
                    this.waitingForConfirmation = false;

                    try {
                        const response = await fetch(`/api/chats/${this.chatUuid}/message`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ message })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Reload chat to get updated messages
                            await this.loadChat();

                            // Check if we need confirmation
                            if (data.requires_confirmation) {
                                this.waitingForConfirmation = true;
                            }
                        } else {
                            alert('Error: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Failed to send message:', error);
                        alert('Failed to send message');
                    } finally {
                        this.sending = false;
                    }
                },

                async continueWorkflow() {
                    this.sending = true;
                    this.waitingForConfirmation = false;

                    try {
                        const response = await fetch(`/api/chats/${this.chatUuid}/continue`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            await this.loadChat();
                        }
                    } catch (error) {
                        console.error('Failed to continue workflow:', error);
                    } finally {
                        this.sending = false;
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                },

                formatTime(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }
    </script>
</x-app-layout>
