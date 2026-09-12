@props([
    'label',
    'value',
    'hint' => null,
    'accent' => 'default',
    'money' => false,
])

@php
    $accentClass = match ($accent) {
        'emerald', 'up' => 'up',
        'amber', 'gold' => 'gold',
        default => '',
    };
@endphp

<div class="stat">
    <p class="stat-figure {{ $accentClass }}">
        @if ($money)
            <x-money :amount="$value" :size="18" />
        @else
            {{ $value }}
        @endif
    </p>
    <p class="stat-caption">{{ $label }}</p>
    @if ($hint)
        <p class="text-xs mt-1" style="color: var(--paper-faint);">{{ $hint }}</p>
    @endif
</div>
