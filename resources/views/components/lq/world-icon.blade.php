@props([
    'name' => null,
    'fallback' => '🌍',
])

@php
    $iconName = is_string($name) ? trim($name) : '';

    // Keep database-controlled component names on an explicit allow-list.
    // This prevents stale or mistyped identifiers from becoming visible text
    // or attempting to resolve arbitrary Blade components.
    $supportedHeroicons = [
        'heroicon-o-academic-cap',
        'heroicon-o-arrow-path',
        'heroicon-o-banknotes',
        'heroicon-o-beaker',
        'heroicon-o-bolt',
        'heroicon-o-book-open',
        'heroicon-o-brain',
        'heroicon-o-calculator',
        'heroicon-o-chat-bubble-left-right',
        'heroicon-o-code-bracket',
        'heroicon-o-cpu-chip',
        'heroicon-o-device-phone-mobile',
        'heroicon-o-fire',
        'heroicon-o-globe-alt',
        'heroicon-o-heart',
        'heroicon-o-light-bulb',
        'heroicon-o-paint-brush',
        'heroicon-o-photo',
        'heroicon-o-play',
        'heroicon-o-puzzle-piece',
        'heroicon-o-rocket-launch',
        'heroicon-o-shield-check',
        'heroicon-o-sparkles',
        'heroicon-o-star',
        'heroicon-o-trophy',
        'heroicon-o-user-group',
        'heroicon-o-users',
    ];

    $isSupportedHeroicon = in_array($iconName, $supportedHeroicons, true);
    $isIconIdentifier = preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $iconName) === 1;
@endphp

@if ($isSupportedHeroicon)
    <x-dynamic-component
        :component="$iconName"
        {{ $attributes->class(['inline-block shrink-0']) }}
        aria-hidden="true"
    />
@elseif ($iconName !== '' && ! $isIconIdentifier)
    <span
        {{ $attributes->class(['inline-flex items-center justify-center']) }}
        aria-hidden="true"
    >{{ $iconName }}</span>
@else
    <span
        {{ $attributes->class(['inline-flex items-center justify-center']) }}
        aria-hidden="true"
    >{{ $fallback }}</span>
@endif
