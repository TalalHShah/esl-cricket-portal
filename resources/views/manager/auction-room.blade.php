@extends('layouts.auction-room')

@section('title', $auctionSession->player?->name ?? $auctionSession->name)

@section('content')
    @php
        $player = $auctionSession->player;
        $isLive = $auctionSession->status === 'live';
        $isScheduled = $auctionSession->status === 'scheduled';
        $isPaused = $auctionSession->status === 'paused';
        $isOver = in_array($auctionSession->status, ['completed', 'cancelled']);
        $minimumBid = $auctionSession->current_bid > 0
            ? $auctionSession->current_bid + $auctionSession->bid_increment
            : $auctionSession->starting_bid;
    @endphp

    {{-- Player identity block --}}
    <div class="flex items-start gap-8 mb-10">
        <div class="player-portrait" style="width: 140px; height: 180px; flex-shrink: 0;">
            @if($player?->image)
                <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
            @else
                <div class="initials">{{ $player ? strtoupper(substr($player->name, 0, 2)) : '—' }}</div>
            @endif
        </div>
        <div class="flex-1">
            <p class="eyebrow {{ $isLive ? 'live' : 'gold' }} mb-2">
                {{ $isLive ? 'Live Now' : ($isScheduled ? 'Scheduled' : ($isPaused ? 'Paused' : 'Concluded')) }}
            </p>
            <h1 class="font-display text-4xl md:text-5xl font-semibold mb-2" style="color: var(--paper);">{{ $player?->name ?? $auctionSession->name }}</h1>
            @if($player)
                <p class="text-base" style="color: var(--paper-dim);">{{ $player->role }} &nbsp;—&nbsp; {{ $player->country }} &nbsp;—&nbsp; {{ $player->tier }}</p>
                <p class="text-sm mt-1" style="color: var(--paper-faint);">Base value {{ number_format((float) $player->base_value, 0) }}</p>
            @endif
        </div>
    </div>

    {{-- Bid state --}}
    <div class="masthead mb-10">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 sm:gap-8">
            <div>
                <p class="stat-caption mb-2">Current Bid</p>
                <p class="stat-figure gold" style="font-size: 2.25rem;">{{ number_format((float) $auctionSession->current_bid ?: $auctionSession->starting_bid, 0) }}</p>
                @if($auctionSession->highestBidder)
                    <p class="text-sm mt-2" style="color: var(--paper-dim);">Leading — {{ $auctionSession->highestBidder->name }}</p>
                @endif
            </div>
            <div class="sm:text-right">
                <p class="stat-caption mb-2">Bid Increment</p>
                <p class="stat-figure" style="font-size: 2.25rem;">{{ number_format((float) $auctionSession->bid_increment, 0) }}</p>
                @if($team)
                    <p class="text-sm mt-2" style="color: var(--paper-dim);">Your remaining budget — {{ number_format($team->remainingBudget(), 0) }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- State-dependent action area --}}
    @if($isOver)
        <div class="card-section p-10 text-center">
            <p class="eyebrow gold mb-3">Auction Concluded</p>
            @if($auctionSession->highestBidder)
                <p class="font-display text-2xl font-semibold" style="color: var(--paper);">
                    Sold to {{ $auctionSession->highestBidder->name }} for {{ number_format((float) $auctionSession->current_bid, 0) }}
                </p>
            @else
                <p class="text-sm" style="color: var(--paper-faint);">This player went unsold.</p>
            @endif
        </div>

    @elseif($isScheduled)
        <div class="card-section p-10 text-center">
            <p class="eyebrow gold mb-3">Auction Has Not Started</p>
            <p class="text-sm mb-6" style="color: var(--paper-faint);">Join now and you'll be seated at the table the moment bidding opens.</p>
            @if(!$joined)
                <form method="POST" action="{{ route('manager.auction.join', $auctionSession) }}">
                    @csrf
                    <button type="submit" class="btn-accent px-8 py-3">Join Auction Room</button>
                </form>
            @else
                <p class="status-pill" style="display:inline-block;">You Are Seated — Waiting to Start</p>
            @endif
        </div>

    @elseif($isPaused)
        <div class="card-section p-10 text-center">
            <p class="eyebrow gold mb-3">Auction Paused</p>
            <p class="text-sm" style="color: var(--paper-faint);">The auctioneer has paused bidding. Stay in the room — it will resume shortly.</p>
        </div>

    @elseif($isLive && !$joined)
        <div class="card-section p-10 text-center">
            <p class="eyebrow live mb-3">Bidding Is Live</p>
            <p class="text-sm mb-6" style="color: var(--paper-faint);">Join the room to place bids on this player.</p>
            <form method="POST" action="{{ route('manager.auction.join', $auctionSession) }}">
                @csrf
                <button type="submit" class="btn-accent px-8 py-3">Join Auction Room</button>
            </form>
        </div>

    @elseif($isLive && $joined)
        @if($team)
            <div class="card-section p-8">
                <div class="flex items-center justify-between gap-6">
                    <div>
                        <p class="eyebrow gold mb-1">Next Bid</p>
                        <p class="stat-figure" style="font-size: 2.25rem;">{{ number_format($minimumBid, 0) }}</p>
                    </div>
                    <form method="POST" action="{{ route('manager.auction.bid', $auctionSession) }}">
                        @csrf
                        <button type="submit" class="btn-accent px-10 py-4 text-base">Place Bid</button>
                    </form>
                </div>
            </div>
        @else
            <div class="card-section p-10 text-center" style="color: var(--paper-faint);">
                You are not assigned to manage a team, so you cannot bid.
            </div>
        @endif
    @endif
@endsection
