<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center bg-slate-900 text-white relative overflow-hidden px-4">
        
        {{-- Background Glows --}}
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-600/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 left-1/3 w-80 h-80 bg-cyan-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <x-lq.glass-card class="w-full max-w-md p-8 relative z-10 border border-slate-700/50 bg-slate-800/60 backdrop-blur-md rounded-3xl shadow-2xl text-center">
            
            <div class="mb-6">
                <span class="text-5xl mb-2 inline-block">🚀</span>
                <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">
                    Explorer Login
                </h1>
                <p class="text-slate-400 text-sm mt-1">Enter your Explorer Name and 4-digit PIN</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                {{-- Username --}}
                <div class="text-left">
                    <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Explorer Username</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="off"
                        class="w-full px-4 py-3.5 bg-slate-900/80 border border-slate-700 rounded-2xl text-white text-lg placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent transition"
                        placeholder="e.g. StarCaptain99">
                    @error('username')
                        <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- PIN --}}
                <div class="text-left">
                    <label for="pin" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">4-Digit Secret PIN</label>
                    <input id="pin" type="password" name="pin" maxlength="4" inputmode="numeric" pattern="[0-9]*" required
                        class="w-full px-4 py-3.5 text-center text-2xl tracking-[0.5em] bg-slate-900/80 border border-slate-700 rounded-2xl text-cyan-400 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent transition"
                        placeholder="••••">
                    @error('pin')
                        <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Launch Button --}}
                <button type="submit" 
                    class="w-full py-4 px-6 rounded-2xl text-lg font-extrabold text-white bg-gradient-to-r from-cyan-500 via-blue-600 to-purple-600 hover:from-cyan-400 hover:to-purple-500 transform active:scale-[0.98] transition duration-150 shadow-lg shadow-cyan-500/25">
                    Launch My Universe 🌌
                </button>
            </form>
        </x-lq.glass-card>
    </div>
</x-guest-layout>