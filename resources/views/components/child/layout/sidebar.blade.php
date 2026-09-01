@props([
    'active' => 'dashboard',
])

<aside
    class="lq-sidebar"
    aria-label="LearnQuest navigation"
>

    {{-- Brand --}}
    <div class="lq-brand">

        <div
            class="lq-brand-mark"
            aria-hidden="true"
        >
            🚀
        </div>

        <div class="lq-brand-name">
            Learn<span>Quest</span>
        </div>

    </div>


    {{-- Navigation --}}
    <nav
        class="lq-sidebar-nav"
        aria-label="Main navigation"
    >

        <a
            href="{{ route('child.dashboard') }}"
            class="lq-nav-link {{ $active === 'dashboard' ? 'is-active' : '' }}"
            @if($active === 'dashboard')
                aria-current="page"
            @endif
        >
            <span
                class="lq-nav-icon"
                aria-hidden="true"
            >
                🌌
            </span>

            <span>
                My Universe
            </span>
        </a>


        <a
            href="{{ route('child.explore') }}"
            class="lq-nav-link {{ $active === 'explore' ? 'is-active' : '' }}"
            @if($active === 'explore')
                aria-current="page"
            @endif
        >
            <span
                class="lq-nav-icon"
                aria-hidden="true"
            >
                🧭
            </span>

            <span>
                Explorer
            </span>
        </a>


        <a
            href="{{ route('child.missions') }}"
            class="lq-nav-link {{ $active === 'worlds' ? 'is-active' : '' }}"
            @if($active === 'worlds')
                aria-current="page"
            @endif
        >
            <span
                class="lq-nav-icon"
                aria-hidden="true"
            >
                🌍
            </span>

            <span>
                Worlds
            </span>
        </a>


        <a
            href="{{ route('child.missions') }}"
            class="lq-nav-link {{ $active === 'courses' ? 'is-active' : '' }}"
            @if($active === 'courses')
                aria-current="page"
            @endif
        >
            <span
                class="lq-nav-icon"
                aria-hidden="true"
            >
                📚
            </span>

            <span>
                Courses
            </span>
        </a>

    </nav>


    {{-- Nova sidebar card --}}
    <div class="lq-sidebar-bottom">

        <x-lq.glass-card
            padding="sm"
            class="lq-sidebar-nova"
        >

            <div class="lq-row">

                <div class="lq-nova-avatar">
                    🤖
                </div>

                <div class="lq-grow">

                    <div class="lq-card-title">
                        Nova
                    </div>

                    <div class="lq-card-meta">
                        Ready to help
                    </div>

                </div>

            </div>

        </x-lq.glass-card>

    </div>

</aside>