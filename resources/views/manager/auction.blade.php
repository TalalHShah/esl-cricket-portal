@extends('layouts.manager')

@section('title', 'Auction')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Player Auction</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Auction Sessions</h1>
    </div>

    @if($team)
        <div class="grid grid-cols-1 gap-4 mb-10 sm:grid-cols-3">
            <div class="stat">
                <p class="stat-figure gold"><x-money :amount="$team->budget" :size="18" /></p>
                <p class="stat-caption">Total Budget</p>
            </div>
            <div class="stat">
                <p class="stat-figure up"><x-money :amount="$team->remainingBudget()" :size="18" /></p>
                <p class="stat-caption">Available to Bid</p>
            </div>
            <div class="stat">
                <p class="stat-figure"><x-money :amount="$team->spent" :size="18" /></p>
                <p class="stat-caption">Already Spent</p>
            </div>
        </div>
    @endif

    {{-- Live Sessions --}}
    <div class="mb-10">
        <p class="eyebrow live mb-4">Live &amp; In Progress</p>
        @forelse ($liveSessions as $session)
            <a href="{{ route('manager.auction.room', $session) }}" class="masthead lift-on-hover block mb-4">
                <div class="flex items-center justify-between gap-8">
                    <div>
                        <span class="status-pill live mb-3" style="display:inline-block;">{{ strtoupper($session->status) }}</span>
                        <h2 class="font-display text-3xl font-semibold mt-3 mb-1" style="color: var(--paper);">{{ $session->player?->name ?? $session->name }}</h2>
                        <p class="text-sm" style="color: var(--paper-dim);">{{ $session->player?->role }} &nbsp;—&nbsp; {{ $session->player?->country }}</p>
                    </div>
                    <div class="text-right">
                        <p class="stat-caption mb-1">Current Bid</p>
                        <p class="stat-figure gold"><x-money :amount="$session->current_bid ?: $session->starting_bid" :size="18" /></p>
                        @if($session->highestBidder)
                            <p class="text-xs mt-1" style="color: var(--paper-faint);">Leading — {{ $session->highestBidder->name }}</p>
                        @endif
                    </div>
                </div>
                <p class="eyebrow gold mt-4">Enter Auction Room &rarr;</p>
            </a>
        @empty
            <div class="card-section p-10 text-center" style="color: var(--paper-faint);">No live auctions right now</div>
        @endforelse
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        {{-- Upcoming --}}
        <div>
            <p class="eyebrow gold mb-4">Scheduled</p>
            <div class="card-section">
                @forelse ($upcomingSessions as $session)
                    <a href="{{ route('manager.auction.room', $session) }}" class="news-row row-hover px-6 block">
                        <p class="font-semibold" style="color: var(--paper);">{{ $session->player?->name ?? $session->name }}</p>
                        <p class="text-xs mt-1" style="color: var(--paper-faint);">Starting bid — <x-money :amount="$session->starting_bid" /></p>
                    </a>
                @empty
                    <div class="p-10 text-center" style="color: var(--paper-faint);">No scheduled auctions</div>
                @endforelse
            </div>
        </div>

        {{-- Completed --}}
        <div>
            <p class="eyebrow gold mb-4">Recently Sold</p>
            <div class="card-section">
                @forelse ($completedSessions as $session)
                    <div class="news-row px-6 flex items-center justify-between">
                        <div>
                            <p class="font-semibold" style="color: var(--paper);">{{ $session->player?->name ?? $session->name }}</p>
                            <p class="text-xs mt-1" style="color: var(--paper-faint);">{{ $session->highestBidder?->name ?? '—' }}</p>
                        </div>
                        <p class="text-sm font-semibold" style="color: var(--gold);"><x-money :amount="$session->current_bid" /></p>
                    </div>
                @empty
                    <div class="p-10 text-center" style="color: var(--paper-faint);">No completed auctions yet</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
