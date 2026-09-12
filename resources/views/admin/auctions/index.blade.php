@extends('layouts.admin')

@section('title', 'Auction Sessions')

@section('content')
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="eyebrow gold mb-2">Auction</p>
            <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Auction Sessions</h1>
            <p class="text-sm mt-2" style="color: var(--paper-faint);">{{ $sessions->total() }} session{{ $sessions->total() !== 1 ? 's' : '' }} — only one can be live at a time</p>
        </div>
        <div class="flex items-center gap-3">
            @include('partials.sort-bar', ['current' => $sort, 'sortId' => 'auctions', 'options' => [
                'created_desc' => 'Newest First',
                'status' => 'Status',
                'bid_desc' => 'Current Bid — High to Low',
            ]])
            <a href="{{ route('admin.auctions.create') }}" class="btn-accent px-5 py-3 whitespace-nowrap">+ New Auction</a>
        </div>
    </div>

    <div class="card-section overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Status</th>
                    <th style="text-align:right;">Current Bid</th>
                    <th>Leading Team</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sessions as $session)
                    <tr>
                        <td>
                            <p class="font-semibold" style="color: var(--paper);">{{ $session->player?->name ?? $session->name }}</p>
                            <p class="text-xs" style="color: var(--paper-faint);">Starting bid — {{ number_format((float) $session->starting_bid, 0) }}</p>
                        </td>
                        <td>
                            <span class="status-pill {{ $session->status === 'live' ? 'live' : ($session->status === 'completed' ? 'confirmed' : 'pending') }}">
                                {{ strtoupper($session->status) }}
                            </span>
                        </td>
                        <td style="text-align:right; color: var(--gold); font-weight: 600;">
                            {{ number_format((float) $session->current_bid, 0) }}
                        </td>
                        <td style="color: var(--paper-dim);">{{ $session->highestBidder?->name ?? '—' }}</td>
                        <td style="text-align:right;">
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                @if($session->status === 'scheduled')
                                    <form method="POST" action="{{ route('admin.auctions.start', $session) }}">
                                        @csrf
                                        <button type="submit" class="btn-accent px-3 py-1.5 text-xs">Start</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.auctions.destroy', $session) }}" onsubmit="return confirm('Delete this scheduled auction?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-ghost px-3 py-1.5 text-xs">Delete</button>
                                    </form>
                                @elseif($session->status === 'live')
                                    <form method="POST" action="{{ route('admin.auctions.pause', $session) }}">
                                        @csrf
                                        <button type="submit" class="btn-ghost px-3 py-1.5 text-xs">Pause</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.auctions.complete', $session) }}" onsubmit="return confirm('Conclude this auction? The player will be sold to the highest bidder.');">
                                        @csrf
                                        <button type="submit" class="btn-accent px-3 py-1.5 text-xs">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.auctions.cancel', $session) }}" onsubmit="return confirm('Cancel this auction?');">
                                        @csrf
                                        <button type="submit" class="btn-ghost px-3 py-1.5 text-xs" style="color: var(--live);">Cancel</button>
                                    </form>
                                @elseif($session->status === 'paused')
                                    <form method="POST" action="{{ route('admin.auctions.resume', $session) }}">
                                        @csrf
                                        <button type="submit" class="btn-accent px-3 py-1.5 text-xs">Resume</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.auctions.complete', $session) }}" onsubmit="return confirm('Conclude this auction? The player will be sold to the highest bidder.');">
                                        @csrf
                                        <button type="submit" class="btn-accent px-3 py-1.5 text-xs">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.auctions.cancel', $session) }}" onsubmit="return confirm('Cancel this auction?');">
                                        @csrf
                                        <button type="submit" class="btn-ghost px-3 py-1.5 text-xs" style="color: var(--live);">Cancel</button>
                                    </form>
                                @else
                                    <span class="text-xs" style="color: var(--paper-faint);">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No auction sessions yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $sessions->links() }}
    </div>
@endsection
