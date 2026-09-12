@extends('layouts.app')

@section('title', $team->name)

@section('content')
    <div class="masthead mb-10">
        <div class="flex items-start gap-8">
            @if($team->logo)
                <div class="crest" style="width:88px;height:88px;">
                    <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="crest" style="width:88px;height:88px; font-size: 2rem;">
                    {{ $team->short_name ?? strtoupper(substr($team->name, 0, 1)) }}
                </div>
            @endif
            <div class="flex-1">
                <p class="eyebrow gold mb-2">{{ $team->manager ? 'Managed by ' . $team->manager->name : 'Unmanaged' }}{{ $team->short_name ? ' — ' . $team->short_name : '' }}</p>
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

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {{-- Squad --}}
        <div class="lg:col-span-2">
            <p class="eyebrow gold mb-4">Squad ({{ $players->count() }})</p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @forelse ($players as $player)
                    <a href="{{ route('players.show', $player) }}" class="card-section lift-on-hover p-3">
                        <div class="player-portrait mb-3" style="aspect-ratio: 3/4;">
                            @if($player->image)
                                <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                            @else
                                <div class="initials">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                            @endif
                        </div>
                        <p class="text-sm font-semibold truncate" style="color: var(--paper);">{{ $player->name }}</p>
                        <p class="text-xs" style="color: var(--paper-faint);">{{ $player->typeLabel() }}</p>
                        <p class="text-sm font-semibold mt-1" style="color: var(--gold);"><x-money :amount="$player->current_value" /></p>
                    </a>
                @empty
                    <div class="col-span-full card-section p-10 text-center" style="color: var(--paper-faint);">No players in this squad</div>
                @endforelse
            </div>
        </div>

        {{-- Recent matches --}}
        <div>
            <p class="eyebrow gold mb-4">Recent Matches</p>
            <div class="card-section">
                @forelse ($matches as $match)
                    <a href="{{ route('matches.show', $match) }}" class="fixture-row row-hover block">
                        <p class="eyebrow mb-2">{{ optional($match->match_date)->format('d M Y') }}</p>
                        <p class="text-base font-semibold mb-2" style="color: var(--paper);">
                            {{ $match->homeTeam?->short_name ?? $match->homeTeam?->name }}
                            <span style="color: var(--paper-faint); font-weight: 400;"> vs </span>
                            {{ $match->awayTeam?->short_name ?? $match->awayTeam?->name }}
                        </p>
                        <span class="status-pill {{ $match->status === 'confirmed' ? 'confirmed' : 'pending' }}">{{ ucfirst($match->status) }}</span>
                    </a>
                @empty
                    <div class="p-10 text-center" style="color: var(--paper-faint);">No matches played yet</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
