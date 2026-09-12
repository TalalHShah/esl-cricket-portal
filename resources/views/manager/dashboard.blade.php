@extends('layouts.manager')

@section('title', 'Manager Home')

@section('content')
    @if(!$team)
        <div class="card-section p-12 text-center" style="color: var(--paper-faint);">
            <p class="text-lg">You are not currently assigned to manage a team.</p>
            <p class="text-sm mt-2">Contact the league administrator to be assigned a team.</p>
        </div>
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
                    <p class="eyebrow gold mb-2">Welcome back, {{ auth()->user()->name }}</p>
                    <h1 class="font-display text-4xl md:text-5xl font-semibold mb-3" style="color: var(--paper);">{{ $team->name }}</h1>
                    <p class="text-lg" style="color: var(--paper-dim);">Manage your squad, scout new talent, and dominate the transfer market.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-10 sm:grid-cols-2 lg:grid-cols-4">
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
            <div class="stat">
                <p class="stat-figure">{{ $squadCount }}</p>
                <p class="stat-caption">Squad Size</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-10 md:grid-cols-4">
            <a href="{{ route('manager.scouts') }}" class="card-section lift-on-hover p-5 text-center">
                <p class="font-semibold text-sm" style="color: var(--paper);">Scout Players</p>
            </a>
            <a href="{{ route('manager.auction') }}" class="card-section lift-on-hover p-5 text-center">
                <p class="font-semibold text-sm" style="color: var(--paper);">Live Auction</p>
            </a>
            <a href="{{ route('manager.transfers') }}" class="card-section lift-on-hover p-5 text-center">
                <p class="font-semibold text-sm" style="color: var(--paper);">Transfer Market</p>
            </a>
            <a href="{{ route('manager.fixtures') }}" class="card-section lift-on-hover p-5 text-center">
                <p class="font-semibold text-sm" style="color: var(--paper);">Fixtures</p>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <p class="eyebrow gold mb-4">Top Valued Players</p>
                <div class="card-section">
                    @forelse ($topPlayers as $player)
                        <div class="news-row px-6 flex items-center gap-4">
                            <div class="player-portrait" style="width: 48px; height: 60px; flex-shrink: 0;">
                                @if($player->image)
                                    <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                                @else
                                    <div class="initials" style="font-size: 0.8rem;">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold" style="color: var(--paper);">{{ $player->name }}</p>
                                <p class="text-xs" style="color: var(--paper-faint);">{{ $player->role }} &nbsp;—&nbsp; {{ $player->tier }}</p>
                            </div>
                            <p class="text-base font-semibold" style="color: var(--gold);"><x-money :amount="$player->current_value" /></p>
                        </div>
                    @empty
                        <div class="p-10 text-center" style="color: var(--paper-faint);">No players in squad yet — visit Scouts to sign players</div>
                    @endforelse
                </div>
            </div>

            <div>
                <p class="eyebrow gold mb-4">Fixtures</p>
                <div class="card-section">
                    @forelse ($upcomingMatches as $match)
                        <div class="fixture-row">
                            <p class="eyebrow mb-2">{{ $match->match_date?->format('d M Y') }}</p>
                            <p class="text-sm font-semibold" style="color: var(--paper);">
                                {{ $match->homeTeam?->short_name ?? $match->homeTeam?->name }}
                                <span style="color: var(--paper-faint); font-weight: 400;"> vs </span>
                                {{ $match->awayTeam?->short_name ?? $match->awayTeam?->name }}
                            </p>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm" style="color: var(--paper-faint);">No fixtures scheduled</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
@endsection
