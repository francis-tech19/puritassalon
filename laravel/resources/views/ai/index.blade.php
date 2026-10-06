@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="aiChat()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">AI Salon Advisor</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Powered by Google Gemini AI. Ask questions about salon performance, staffing, promotions, and operations.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-black bg-pink-100 text-[#7A1C49] border border-pink-300 flex items-center gap-1.5">
                <i data-lucide="bot" class="w-4 h-4"></i> Gemini AI Active
            </span>
        </div>
    </div>

    <!-- Suggested Question Chips -->
    <div class="card p-4">
        <span class="text-xs font-black uppercase text-gray-400 block mb-2">Quick Advisory Prompts:</span>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="askPreset('How much did we sell today, and what should we focus on before closing?')" class="px-3 py-1.5 rounded-xl bg-pink-50 border border-pink-200 text-xs font-extrabold text-[#7A1C49] hover:bg-[#7A1C49] hover:text-white transition">
                Sales Today
            </button>
            <button type="button" @click="askPreset('Summarize this month\'s sales, expenses, and estimated profit.')" class="px-3 py-1.5 rounded-xl bg-pink-50 border border-pink-200 text-xs font-extrabold text-[#7A1C49] hover:bg-[#7A1C49] hover:text-white transition">
                Sales This Month
            </button>
            <button type="button" @click="askPreset('Review today\'s appointments and suggest ways to fill unused time.')" class="px-3 py-1.5 rounded-xl bg-pink-50 border border-pink-200 text-xs font-extrabold text-[#7A1C49] hover:bg-[#7A1C49] hover:text-white transition">
                Today\'s Appointments
            </button>
            <button type="button" @click="askPreset('Which inventory items are critically low at 1 to 3 units?')" class="px-3 py-1.5 rounded-xl bg-red-50 border border-red-200 text-xs font-extrabold text-red-800 hover:bg-red-700 hover:text-white transition">
                Critical Inventory
            </button>
            <button type="button" @click="askPreset('Which inventory items should we reorder first based on low stock levels?')" class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-xs font-extrabold text-amber-800 hover:bg-amber-600 hover:text-white transition">
                Reorder Advice
            </button>
            <button type="button" @click="askPreset('Which customers and services should we prioritize to increase repeat visits?')" class="px-3 py-1.5 rounded-xl bg-pink-50 border border-pink-200 text-xs font-extrabold text-[#7A1C49] hover:bg-[#7A1C49] hover:text-white transition">
                Customer Growth
            </button>
            <button type="button" @click="askPreset('Create a promotion for our slowest weekdays using our current salon performance.')" class="px-3 py-1.5 rounded-xl bg-pink-50 border border-pink-200 text-xs font-extrabold text-[#7A1C49] hover:bg-[#7A1C49] hover:text-white transition">
                Slow-Day Promotion
            </button>
        </div>
    </div>

    <!-- Chat Messages Window -->
    <div class="card p-6 min-h-[400px] flex flex-col justify-between">
        <div class="space-y-4 max-h-[480px] overflow-y-auto pr-2" id="chatContainer">
            <!-- Welcome AI message -->
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#7A1C49] text-white flex items-center justify-center shrink-0">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
                <div class="p-4 rounded-3xl rounded-tl-sm bg-pink-50 border border-pink-200 text-gray-900 font-semibold text-sm max-w-2xl">
                    <p class="font-extrabold text-[#7A1C49] mb-1">Purita AI Salon Advisor</p>
                    <p>Hello! I analyze your appointments, sales trends, and inventory levels to provide practical business recommendations. How can I help you grow Purita's Beauty Lounge today?</p>
                </div>
            </div>

            <!-- Dynamic Conversation History -->
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.sender === 'user' ? 'flex items-start justify-end gap-3' : 'flex items-start gap-3'">
                    <template x-if="msg.sender === 'ai'">
                        <div class="w-10 h-10 rounded-2xl bg-[#7A1C49] text-white flex items-center justify-center shrink-0">
                            <i data-lucide="bot" class="w-5 h-5"></i>
                        </div>
                    </template>

                    <div :class="msg.sender === 'user' ? 'bg-[#7A1C49] text-white rounded-3xl rounded-tr-sm' : 'bg-white border-2 border-gray-200 text-gray-900 rounded-3xl rounded-tl-sm'"
                         class="p-4 font-semibold text-sm max-w-2xl whitespace-pre-line shadow-sm" x-text="msg.text">
                    </div>

                    <template x-if="msg.sender === 'user'">
                        <div class="w-10 h-10 rounded-2xl bg-gray-200 text-gray-700 flex items-center justify-center font-bold text-sm shrink-0">
                            You
                        </div>
                    </template>
                </div>
            </template>

            <!-- Loading Spinner -->
            <div x-show="loading" style="display: none;" class="flex items-center gap-3 text-sm font-bold text-gray-500">
                <div class="w-8 h-8 rounded-full border-4 border-[#7A1C49] border-t-transparent animate-spin"></div>
                <span>Analyzing salon metrics & preparing recommendations...</span>
            </div>
        </div>

        <!-- Input Bar -->
        <form @submit.prevent="sendMessage()" class="mt-6 pt-4 border-t-2 border-gray-100 flex gap-3">
            <input type="text" x-model="inputPrompt" :disabled="loading" placeholder="Ask a question about your salon business..."
                   class="form-input flex-1 font-bold text-base" required>
            <button type="submit" :disabled="loading" class="btn btn-primary px-6 shadow-md">
                <i data-lucide="send" class="w-5 h-5"></i>
                <span class="hidden sm:inline">Ask AI</span>
            </button>
        </form>
    </div>

</div>

<script>
    function aiChat() {
        return {
            inputPrompt: '',
            loading: false,
            messages: [],
            askPreset(promptText) {
                this.inputPrompt = promptText;
                this.sendMessage();
            },
            async sendMessage() {
                if (!this.inputPrompt.trim() || this.loading) return;

                const userText = this.inputPrompt;
                this.messages.push({ sender: 'user', text: userText });
                this.inputPrompt = '';
                this.loading = true;

                try {
                    const res = await fetch('{{ route("ai.ask") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ prompt: userText })
                    });
                    const data = await res.json();
                    if (data.insight) {
                        this.messages.push({ sender: 'ai', text: data.insight });
                    } else {
                        this.messages.push({ sender: 'ai', text: 'Sorry, I could not generate an answer at this time.' });
                    }
                } catch (e) {
                    this.messages.push({ sender: 'ai', text: 'An error occurred while contacting the AI service. Please try again.' });
                } finally {
                    this.loading = false;
                    this.$nextTick(() => {
                        const container = document.getElementById('chatContainer');
                        if (container) container.scrollTop = container.scrollHeight;
                    });
                }
            }
        };
    }
</script>
@endsection
