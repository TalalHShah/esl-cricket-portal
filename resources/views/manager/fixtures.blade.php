@extends('layouts.manager')

@section('title', 'Fixtures')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white mb-2 flex items-center gap-2"><span>🏟️</span> Fixtures</h1>
        <p class="text-lg text-slate-400">
            @if($team) {{ $team->name }}'s matches @else No team assigned @endif
        </p>
    </div>

    <div class="card-section rounded">
        <div class="divide-y" style="border-color: var(--border);">
            @forelse ($matches as $match)
                <div class="p-6 border-l-4" style="border-left-color: var(--accent);">
                    <p class="text-sm text-slate-400 mb-2 font-semibold">{{ $match->match_date?->format('d M Y, H:i') }} • {{ $match->venue ?? 'TBD' }}</p>
                    <h3 class="text-2xl font-black text-white mb-3">
                        {{ $match->homeTeam?->name ?? 'TBD' }}
                        <span class="text-slate-500 font-normal text-lg">vs</span>
                        {{ $match->awayTeam?->name ?? 'TBD' }}
                    </h3>
                    <span class="px-3 py-1 text-xs font-bold text-white rounded" style="background-color: var(--primary);">
                        {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                    </span>
                </div>
            @empty
                <div class="p-12 text-center text-slate-500">No fixtures scheduled yet</div>
            @endforelse
        </div>
    </div>

    @if(method_exists($matches, 'links'))
        <div class="mt-6">
            {{ $matches->links() }}
        </div>
    @endif
@endsection
