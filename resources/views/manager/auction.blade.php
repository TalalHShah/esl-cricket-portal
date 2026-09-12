@extends('layouts.manager')

@section('title', 'Live Auction')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white mb-2 flex items-center gap-2"><span>🔨</span> Auction Room</h1>
        <p class="text-lg text-slate-400">Bid for players live during the ESL auction window</p>
    </div>

    @if($team)
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="stat-box">
                <div class="stat-value">{{ number_format((float) $team->budget, 0) }}</div>
                <div class="stat-label">Total Budget</div>
            </div>
            <div class="stat-box">
                <div class="stat-value" style="color: var(--success);">{{ number_format($team->remainingBudget(), 0) }}</div>
                <div class="stat-label">Available to Bid</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ number_format((float) $team->spent, 0) }}</div>
                <div class="stat-label">Already Spent</div>
            </div>
        </div>
    @endif

    {{-- Active Auction --}}
    @if($activeSession)
        <div class="featured-story rounded mb-8">
            <span class="news-badge urgent">🔴 LIVE NOW</span>
            <div class="flex items-center justify-between gap-8 mt-4">
                <div class="flex-1">
                    <h2 class="text-3xl font-black text-white mb-2">{{ $activeSession->player?->name ?? 'Unknown Player' }}</h2>
                    <p class="text-blue-100 mb-1">{{ $activeSession->player?->role }} • {{ $activeSession->player?->country }} • {{ $activeSession->player?->tier }}</p>
                    <p class="text-sm text-blue-200">Base Value: PKR {{ number_format((float) $activeSession->player?->base_value, 0) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase text-blue-200 font-bold">Current Bid</p>
                    <p class="text-4xl font-black" style="color: var(--accent);">
                        {{ number_format((float) $activeSession->current_bid ?: $activeSession->starting_bid, 0) }}
                    </p>
                    @if($activeSession->highestBidder)
                        <p class="text-sm text-blue-200 mt-1">Leading: {{ $activeSession->highestBidder->name }}</p>
                    @endif
                </div>
            </div>

            @if($team && $activeSession->status === 'live')
                <form method="POST" action="{{ route('manager.auction.bid', $activeSession) }}" class="mt-6 flex items-center gap-3">
                    @csrf
                    <button type="submit" class="btn-accent px-8 py-3 text-lg font-black rounded">
                        BID {{ number_format(($activeSession->current_bid > 0 ? $activeSession->current_bid + $activeSession->bid_increment : $activeSession->starting_bid), 0) }}
                    </button>
                    <p class="text-sm text-blue-200">Increment: PKR {{ number_format((float) $activeSession->bid_increment, 0) }}</p>
                </form>
            @elseif($activeSession->status === 'paused')
                <p class="mt-6 text-sm text-blue-200">⏸ Auction is paused by the administrator</p>
            @endif
        </div>
    @else
        <div class="card-section rounded p-12 text-center mb-8">
            <p class="text-2xl mb-2">💤</p>
            <p class="text-lg text-slate-400">No live auction right now</p>
            <p class="text-sm text-slate-500 mt-1">Check back during the scheduled auction window</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Upcoming --}}
        <div class="card-section rounded">
            <div class="card-header flex items-center gap-2">
                <span>⏳</span>
                <h2>Upcoming Auctions</h2>
            </div>
            <div class="divide-y" style="border-color: var(--border);">
                @forelse ($upcomingSessions as $session)
                    <div class="p-5">
                        <p class="font-bold text-white">{{ $session->player?->name ?? $session->name }}</p>
                        <p class="text-xs text-slate-400">Starting bid: PKR {{ number_format((float) $session->starting_bid, 0) }}</p>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-sm">No scheduled auctions</div>
                @endforelse
            </div>
        </div>

        {{-- Completed --}}
        <div class="card-section rounded">
            <div class="card-header flex items-center gap-2">
                <span>✅</span>
                <h2>Recently Sold</h2>
            </div>
            <div class="divide-y" style="border-color: var(--border);">
                @forelse ($completedSessions as $session)
                    <div class="p-5 flex items-center justify-between">
                        <div>
                            <p class="font-bold text-white">{{ $session->player?->name ?? $session->name }}</p>
                            <p class="text-xs text-slate-400">Sold to {{ $session->highestBidder?->name ?? '—' }}</p>
                        </div>
                        <p class="stat-value text-sm">{{ number_format((float) $session->current_bid, 0) }}</p>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-sm">No completed auctions yet</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
