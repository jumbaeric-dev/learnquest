5@props([
    'icon' => null,
    'title' => null,
    'message' => null,
    'action' => null,
    'href' => null,
])

<div
    {{ $attributes->class([
        'lq-empty-state',
    ]) }}
>
    @if($icon)
        <div
            class="lq-empty-state__icon"
            aria-hidden="true"
        >
            {{ $icon }}
        </div>
    @endif

    @if($title)
        <h3 class="lq-empty-state__title">
            {{ $title }}
        </h3>
    @endif

    @if($message)
        <p class="lq-empty-state__message">
            {{ $message }}
        </p>
    @endif

    {{ $slot }}

    @if($action && $href)
        <a
            href="{{ $href }}"
            class="lq-button lq-button--secondary lq-button--sm"
        >
            {{ $action }}
        </a>
    @endif
</div>