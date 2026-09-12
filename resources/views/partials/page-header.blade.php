@props(['title', 'subtitle' => null, 'eyebrow' => null])

<div class="mb-10 flex flex-wrap items-end justify-between gap-4">
    <div>
        @if ($eyebrow)
            <p class="eyebrow gold mb-2">{{ $eyebrow }}</p>
        @endif
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-2 text-sm" style="color: var(--paper-faint);">{{ $subtitle }}</p>
        @endif
    </div>
    @if (! empty($actions))
        <div class="flex items-center gap-2">{{ $actions }}</div>
    @endif
</div>
