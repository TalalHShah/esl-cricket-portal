@extends('layouts.manager')

@section('title', 'Manager Home')

@section('content')
    @if(!$team)
        <div class="card-section rounded p-8 text-center">
            <p class="text-lg text-slate-400">You are not currently assigned to manage a team.</p>
            <p class="text-sm text-slate-500 mt-2">Contact the league administrator to be assigned a team.</p>
        </div>
    @else
        {{-- Team Hero --}}
        <div class="featured-story rounded mb-8">
            <div class="flex items-start gap-6">
                @if($team->logo)
                    <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}"
                         class="w-20 h-20 rounded object-cover flex-shrink-0" style="border: 3px solid var(--accent);">
                @else
                    <div class="team-badge-placeholder text-2xl flex-shrink-0" style="background-color: var(--primary); border-color: var(--accent); width: 80px; height: 80px;">
                        {{ strtoupper(substr($team->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <p class="text-sm font-bold uppercase" style="color: var(--accent);">Welcome back, {{ auth()->user()->name }}</p>
                    <h1 class="text-4xl font-black text-white mt-1 mb-2">{{ $team->name }}</h1>
                    <p class="text-blue-100">Manage your squad, scout new talent, and dominate the transfer market.</p>
                </div>
            </div>
        </div>

        {{-- Budget Stats --}}
        <div class="grid grid-cols-2 gap-4 mb-8 lg:grid-cols-4">
            <div class="stat-box">
                <div class="stat-value">{{ number_format((float) $team->budget, 0) }}</div>
                <div class="stat-label">Total Budget</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ number_format((float) $team->spent, 0) }}</div>
                <div class="stat-label">Spent</div>
            </div>
            <div class="stat-box">
                <div class="stat-value" style="color: var(--success);">{{ number_format($team->remainingBudget(), 0) }}</div>
                <div class="stat-label">Remaining</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $squadCount }}</div>
                <div class="stat-label">Squad Size</div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-2 gap-4 mb-8 md:grid-cols-4">
            <a href="{{ route('manager.scouts') }}" class="card-section rounded p-5 text-center hover:shadow-lg transition">
                <div class="text-3xl mb-2">🔍</div>
                <p class="font-bold text-white text-sm">Scout Players</p>
            </a>
            <a href="{{ route('manager.auction') }}" class="card-section rounded p-5 text-center hover:shadow-lg transition">
                <div class="text-3xl mb-2">🔨</div>
                <p class="font-bold text-white text-sm">Live Auction</p>
            </a>
            <a href="{{ route('manager.transfers') }}" class="card-section rounded p-5 text-center hover:shadow-lg transition">
                <div class="text-3xl mb-2">🔄</div>
                <p class="font-bold text-white text-sm">Transfer Market</p>
            </a>
            <a href="{{ route('manager.fixtures') }}" class="card-section rounded p-5 text-center hover:shadow-lg transition">
                <div class="text-3xl mb-2">🏟️</div>
                <p class="font-bold text-white text-sm">Fixtures</p>
            </a>
        </div>

        {{-- Grid: Top Players + Recent Activity --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Top Players --}}
            <div class="lg:col-span-2">
                <div class="card-section rounded">
                    <div class="card-header flex items-center gap-2">
                        <span>⭐</span>
                        <h2>Top Valued Players</h2>
                    </div>
                    <div class="divide-y" style="border-color: var(--border);">
                        @forelse ($topPlayers as $player)
                            <div class="p-5 flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-white">{{ $player->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $player->role }} • {{ $player->tier }}</p>
                                </div>
                                <p class="stat-value text-base">{{ number_format((float) $player->current_value, 0) }}</p>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-500">No players in squad yet — visit Scouts to sign players</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Upcoming Fixtures --}}
            <div>
                <div class="card-section rounded">
                    <div class="card-header flex items-center gap-2">
                        <span>🏟️</span>
                        <h2>Fixtures</h2>
                    </div>
                    <div class="divide-y" style="border-color: var(--border);">
                        @forelse ($upcomingMatches as $match)
                            <div class="p-5 border-l-4" style="border-left-color: var(--accent);">
                                <p class="text-xs text-slate-400 mb-1">{{ $match->match_date?->format('d M Y') }}</p>
                                <p class="font-bold text-white text-sm">
                                    {{ $match->homeTeam?->short_name ?? $match->homeTeam?->name }}
                                    <span class="text-slate-500 font-normal">vs</span>
                                    {{ $match->awayTeam?->short_name ?? $match->awayTeam?->name }}
                                </p>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-500 text-sm">No fixtures scheduled</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
