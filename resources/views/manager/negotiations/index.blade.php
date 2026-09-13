@extends('layouts.manager')

@section('title', 'Negotiations')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Player Movement</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Negotiations</h1>
        <p class="text-sm mt-2" style="color: var(--paper-faint);">Every offer, counter, and decision for players signed to another team — start one from the <a href="{{ route('manager.transfers') }}" class="underline">Transfer Market</a>.</p>
    </div>

    @if (! $team)
        <div class="card-section p-10 text-center" style="color: var(--paper-faint);">You are not assigned to manage a team.</div>
    @elseif ($negotiations->isEmpty())
        <div class="card-section p-12 text-center" style="color: var(--paper-faint);">No negotiations yet.</div>
    @else
        <div class="card-section">
            @foreach ($negotiations as $transfer)
                @php
                    $youAreBuyer = $transfer->to_team_id === $team->id;
                    $counterparty = $youAreBuyer ? $transfer->fromTeam : $transfer->toTeam;
                    $yourTurn = $transfer->awaitingResponseFrom($team->id);
                @endphp
                <a href="{{ route('manager.negotiations.show', $transfer) }}" class="news-row px-6 flex items-center justify-between gap-4" style="text-decoration:none;">
                    <div class="min-w-0">
                        <p class="font-semibold text-sm truncate" style="color: var(--paper);">{{ $transfer->player?->name }}</p>
                        <p class="text-xs" style="color: var(--paper-faint);">
                            {{ $youAreBuyer ? 'Buying from' : 'Selling to' }} {{ $counterparty?->short_name ?? $counterparty?->name ?? '—' }}
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-semibold mb-1" style="color: var(--gold);"><x-money :amount="$transfer->fee" :size="14" /></p>
                        @if ($transfer->status === 'pending')
                            <span class="status-pill {{ $yourTurn ? 'live' : 'pending' }}">{{ $yourTurn ? 'Your Move' : 'Awaiting Them' }}</span>
                        @else
                            <span class="status-pill {{ $transfer->status === 'approved' ? 'confirmed' : '' }}">{{ ucfirst($transfer->status) }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $negotiations->links() }}
        </div>
    @endif
@endsection
