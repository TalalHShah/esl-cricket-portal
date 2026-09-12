@props([
    'label',
    'value',
    'hint' => null,
    'accent' => 'emerald',
])

@php
    $accents = [
        'emerald' => 'text-emerald-400',
        'sky' => 'text-sky-400',
        'amber' => 'text-amber-400',
        'rose' => 'text-rose-400',
        'violet' => 'text-violet-400',
    ];
    $accentClass = $accents[$accent] ?? $accents['emerald'];
@endphp

<div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $label }}</p>
    <p class="mt-2 text-3xl font-bold {{ $accentClass }}">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
