<div class="space-y-6">

    <div class="px-1">

        <h2 class="text-2xl font-black tracking-tight text-white">
            🗺️ Your Adventure
        </h2>

        <p class="mt-1 text-sm text-white/70">
            Keep exploring to unlock the galaxy.
        </p>

    </div>

    <div class="relative">

        {{-- Adventure Path Line --}}
        <div
            class="absolute left-7 top-8 bottom-8
                   w-1 rounded-full
                   bg-white/30">
        </div>

        <div class="relative space-y-6">

            @foreach($nodes as $node)

            <div
                wire:key="adventure-node-{{ $node['id'] }}"
                class="relative flex items-start gap-4">

                {{-- Node --}}
                <button
                    type="button"
                    wire:click="startNode({{ $node['id'] }})"
                    @disabled($node['status']==='locked' )
                    class="relative z-10
       flex h-14 w-14 shrink-0
       items-center justify-center
       rounded-full
       border-4 border-white
       bg-white
       text-2xl
       shadow-xl
       transition-all
       duration-300
       {{ $node['status'] === 'current'
            ? 'scale-110 ring-4 ring-cyan-300/40'
            : '' }}
       {{ $node['status'] === 'locked'
            ? 'cursor-not-allowed opacity-50 grayscale'
            : 'hover:scale-110 active:scale-95' }}">
                    {{ $node['icon'] }}
                </button>

                {{-- Content --}}
                <div class="min-w-0 flex-1">

                    <div
                        @class([ 'rounded-3xl p-4 backdrop-blur-xl transition' , 'border border-white/70 bg-white/90 shadow-xl'=> $node['status'] !== 'current',

                        'border-2 border-cyan-300 bg-white shadow-2xl shadow-cyan-500/20'
                        => $node['status'] === 'current',
                        ])
                        >

                        <div class="flex items-start justify-between gap-3">

                            <div>

                                <h3 class="text-base font-bold text-slate-900">
                                    {{ $node['title'] }}
                                </h3>

                                <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                    {{ $node['description'] }}
                                </p>

                            </div>

                            <span
                                class="shrink-0
                                           rounded-full
                                           bg-amber-100
                                           px-2.5 py-1
                                           text-xs font-semibold
                                           text-amber-700">
                                +{{ $node['xp'] }} XP
                            </span>

                        </div>

                        @if($node['status'] === 'completed')

                        <div class="mt-3 text-xs font-semibold text-emerald-600">
                            ✓ Completed
                        </div>

                        @elseif($node['status'] === 'current')

                        <div class="mt-3">

                            <x-lq.button
                                type="button"
                                wire:click="startNode({{ $node['id'] }})">
                                🚀 Start Adventure
                            </x-lq.button>

                        </div>

                        @elseif($node['status'] === 'locked')

                        <div class="mt-3 text-xs font-semibold text-slate-400">
                            🔒 Complete the previous adventure
                        </div>

                        @elseif($node['status'] === 'reward')

                        <div class="mt-3 text-xs font-semibold text-purple-600">
                            🏆 Special Reward
                        </div>

                        @endif

                    </div>

                </div>

            </div>

            @endforeach

        </div>

    </div>

</div>