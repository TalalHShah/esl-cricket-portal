@extends('layouts.manager')

@section('title', 'Team Profile')

@section('content')
    @if(!$team)
        <div class="card-section p-12 text-center" style="color: var(--paper-faint);">You are not assigned to manage a team.</div>
    @else
        <div class="masthead mb-10">
            <div class="flex items-start gap-8">
                @if($team->logo)
                    <div class="crest" style="width:88px;height:88px;">
                        <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="crest" style="width:88px;height:88px; font-size: 2rem;">
                        {{ strtoupper(substr($team->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <p class="eyebrow gold mb-2">{{ $team->short_name }}</p>
                    <h1 class="font-display text-4xl md:text-5xl font-semibold mb-3" style="color: var(--paper);">{{ $team->name }}</h1>
                    @if($team->description)
                        <p class="text-lg" style="color: var(--paper-dim); max-width: 46rem;">{{ $team->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-10 sm:grid-cols-3">
            <div class="stat">
                <p class="stat-figure gold"><x-money :amount="$team->budget" :size="18" /></p>
                <p class="stat-caption">Total Budget</p>
            </div>
            <div class="stat">
                <p class="stat-figure"><x-money :amount="$team->spent" :size="18" /></p>
                <p class="stat-caption">Spent</p>
            </div>
            <div class="stat">
                <p class="stat-figure up"><x-money :amount="$team->remainingBudget()" :size="18" /></p>
                <p class="stat-caption">Remaining</p>
            </div>
        </div>

        <p class="eyebrow gold mb-4">Full Squad ({{ $players->count() }})</p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($players as $player)
                <div class="card-section lift-on-hover p-4">
                    <div class="player-portrait mb-3" style="aspect-ratio: 3/4;">
                        @if($player->image)
                            <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                        @else
                            <div class="initials">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                        @endif
                    </div>
                    <p class="text-sm font-semibold truncate" style="color: var(--paper);">{{ $player->name }}</p>
                    <p class="text-xs" style="color: var(--paper-faint);">{{ $player->role }} &nbsp;—&nbsp; {{ $player->tier }}</p>
                    <p class="text-sm font-semibold mt-2" style="color: var(--gold);"><x-money :amount="$player->current_value" /></p>
                </div>
            @empty
                <div class="col-span-full card-section p-12 text-center" style="color: var(--paper-faint);">No players in squad yet</div>
            @endforelse
        </div>
    @endif
@endsection
