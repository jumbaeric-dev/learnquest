<div
    x-data
    class="fixed bottom-24 right-5 z-[110] sm:bottom-8"
>

    {{-- Floating launcher bubble --}}
    @unless($open)
    <button
        type="button"
        wire:click="open"
        class="
            flex h-16 w-16 items-center justify-center
            rounded-full
            bg-gradient-to-br from-indigo-500 to-purple-600
            text-3xl text-white shadow-2xl
            transition duration-300
            hover:scale-110
        "
        aria-label="Chat with Nova"
    >
        🤖
    </button>
    @endunless

    {{-- Chat panel --}}
    @if($open)
    <div
        x-transition.opacity.duration.200ms
        class="
            flex h-[70vh] max-h-[560px] w-[92vw] max-w-sm
            flex-col overflow-hidden rounded-3xl
            border border-white/20 bg-white shadow-2xl
        "
    >

        {{-- Header --}}
        <div class="flex items-center justify-between bg-gradient-to-r from-indigo-500 to-purple-600 px-5 py-4 text-white">

            <div class="flex items-center gap-2">
                <span class="text-2xl">🤖</span>
                <div>
                    <p class="font-bold leading-tight">Nova</p>
                    <p class="text-xs text-white/80 leading-tight">Your learning buddy</p>
                </div>
            </div>

            <button
                type="button"
                wire:click="close"
                class="rounded-full p-1 text-white/80 hover:bg-white/10 hover:text-white"
                aria-label="Close chat"
            >
                ✕
            </button>

        </div>

        {{-- Messages --}}
        <div
            class="flex-1 space-y-3 overflow-y-auto bg-slate-50 px-4 py-4"
            x-ref="messageList"
            x-init="$watch('$wire.messages', () => $nextTick(() => $refs.messageList.scrollTop = $refs.messageList.scrollHeight))"
        >

            @if(empty($messages))
            <div class="rounded-2xl bg-white p-4 text-sm text-slate-600 shadow">
                Hi, I'm Nova! 👋 Ask me anything about what you're learning.
            </div>
            @endif

            @foreach($messages as $message)
            <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div
                    class="
                        max-w-[80%] rounded-2xl px-4 py-2 text-sm shadow
                        {{ $message['role'] === 'user'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white text-slate-700' }}
                    "
                >
                    {{ $message['content'] }}
                </div>
            </div>
            @endforeach

            <div wire:loading wire:target="send" class="flex justify-start">
                <div class="rounded-2xl bg-white px-4 py-2 text-sm text-slate-400 shadow">
                    Nova is thinking
                    <span class="animate-pulse">...</span>
                </div>
            </div>

        </div>

        {{-- Composer --}}
        <div class="border-t border-slate-100 bg-white p-3">

            @if($limitReached)
            <p class="px-2 pb-2 text-center text-xs text-slate-400">
                Nova needs a rest — chat with her again tomorrow! 🌙
            </p>
            @endif

            <form
                wire:submit.prevent="send"
                class="flex items-center gap-2"
            >
                <input
                    type="text"
                    wire:model="draft"
                    wire:loading.attr="disabled"
                    wire:target="send"
                    @disabled($limitReached)
                    placeholder="Ask Nova something..."
                    maxlength="500"
                    class="
                        flex-1 rounded-full border border-slate-200
                        px-4 py-2 text-sm
                        focus:border-indigo-400 focus:outline-none
                        disabled:bg-slate-100
                    "
                />

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="send"
                    @disabled($limitReached)
                    class="
                        flex h-10 w-10 shrink-0 items-center justify-center
                        rounded-full bg-indigo-600 text-white
                        transition hover:bg-indigo-700
                        disabled:cursor-not-allowed disabled:opacity-40
                    "
                    aria-label="Send message"
                >
                    ➤
                </button>
            </form>

        </div>

    </div>
    @endif

</div>
