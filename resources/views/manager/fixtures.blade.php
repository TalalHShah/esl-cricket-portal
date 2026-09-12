@extends('layouts.manager')

@section('title', 'Fixtures')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Schedule</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Fixtures</h1>
        <p class="text-sm mt-2" style="color: var(--paper-faint);">
            @if($team) {{ $team->name }}'s matches @else No team assigned @endif
        </p>
    </div>

    <div class="card-section">
        @forelse ($matches as $match)
            <div class="fixture-row">
                <p class="eyebrow mb-2">{{ $match->match_date?->format('d M Y, H:i') }} &nbsp;—&nbsp; {{ $match->venue ?? 'TBD' }}</p>
                <p class="fixture-teams mb-3">
                    {{ $match->homeTeam?->name ?? 'TBD' }}<span class="vs">vs</span>{{ $match->awayTeam?->name ?? 'TBD' }}
                </p>
                <span class="status-pill {{ $match->status === 'confirmed' ? 'confirmed' : 'pending' }}">
                    {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                </span>
            </div>
        @empty
            <div class="p-12 text-center" style="color: var(--paper-faint);">No fixtures scheduled yet</div>
        @endforelse
    </div>

    @if(method_exists($matches, 'links'))
        <div class="mt-6">
            {{ $matches->links() }}
        </div>
    @endif
@endsection
