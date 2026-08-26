<form method="POST" action="{{ route('logout') }}">
    @csrf

    <button
        type="submit"
        class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-slate-900/40 px-3 py-2 text-sm font-semibold text-slate-300 backdrop-blur-sm transition hover:border-white/20 hover:bg-slate-800/60 hover:text-white focus:outline-none focus:ring-2 focus:ring-cyan-400/60 active:scale-95"
        aria-label="Log out of LearnQuest"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            class="h-4 w-4"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15"
            />
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M18 8.25 21.75 12 18 15.75M21.75 12H9"
            />
        </svg>

        <span>Logout</span>
    </button>
</form>