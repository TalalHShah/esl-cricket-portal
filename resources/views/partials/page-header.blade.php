@props(['title', 'subtitle' => null])

<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-400">{{ $subtitle }}</p>
        @endif
    </div>
    @if (! empty($actions))
        <div class="flex items-center gap-2">{{ $actions }}</div>
    @endif
</div>
