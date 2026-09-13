@extends('layouts.manager')

@section('title', 'Negotiation — ' . ($transfer->player?->name ?? 'Transfer'))

@section('content')
    @php
        $youAreBuyer = $transfer->to_team_id === $team?->id;
        $counterparty = $youAreBuyer ? $transfer->fromTeam : $transfer->toTeam;
        $yourTurn = $transfer->awaitingResponseFrom($team?->id);
        $youMadeLastOffer = $transfer->last_offer_by_team_id === $team?->id;
    @endphp

    <div class="mb-8 flex items-start justify-between flex-wrap gap-4">
        <div>
            <p class="eyebrow gold mb-2"><a href="{{ route('manager.negotiations.index') }}" class="underline">Negotiations</a> / {{ $transfer->player?->name }}</p>
            <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">{{ $transfer->player?->name }}</h1>
            <p class="text-sm mt-1" style="color: var(--paper-faint);">
                {{ $transfer->fromTeam?->name ?? '—' }} <span style="color: var(--paper-faint);">&rarr;</span> {{ $transfer->toTeam?->name ?? '—' }}
            </p>
        </div>
        <div class="text-right">
            <p class="stat-caption mb-1">Current Offer</p>
            <p class="stat-figure gold"><x-money :amount="$transfer->fee" :size="24" /></p>
            @if ($transfer->status === 'pending')
                <span class="status-pill {{ $yourTurn ? 'live' : 'pending' }} mt-2" style="display:inline-block;">{{ $yourTurn ? 'Your Move' : 'Awaiting Response' }}</span>
            @else
                <span class="status-pill {{ $transfer->status === 'approved' ? 'confirmed' : '' }} mt-2" style="display:inline-block;">{{ ucfirst($transfer->status) }}</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <p class="eyebrow gold mb-3">Negotiation Thread</p>
            <div class="card-section p-6 space-y-4">
                @foreach ($transfer->rounds as $round)
                    @php
                        $isYou = $round->team_id === $team?->id;
                        $actionLabel = match ($round->action) {
                            'offer' => 'made an opening offer',
                            'counter' => 'countered',
                            'accept' => 'accepted',
                            'reject' => 'declined',
                            'withdraw' => 'withdrew their offer',
                            default => $round->action,
                        };
                    @endphp
                    <div class="flex items-start gap-3" style="{{ $isYou ? '' : 'flex-direction: row-reverse; text-align: right;' }}">
                        <div class="flex-1 {{ $isYou ? '' : 'flex flex-col items-end' }}">
                            <p class="text-xs mb-1" style="color: var(--paper-faint);">
                                <strong style="color: var(--paper-dim);">{{ $round->team?->short_name ?? $round->team?->name }}</strong>
                                {{ $actionLabel }}
                                &middot; {{ $round->created_at->format('M j, g:i A') }}
                            </p>
                            <div class="card-section p-3" style="display:inline-block; {{ $isYou ? 'border-color: var(--gold-dim);' : '' }}">
                                @if (in_array($round->action, ['offer', 'counter']))
                                    <p class="text-sm font-semibold" style="color: var(--gold);"><x-money :amount="$round->fee" :size="14" /></p>
                                @endif
                                @if ($round->message)
                                    <p class="text-xs mt-1" style="color: var(--paper-dim);">{{ $round->message }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <p class="eyebrow gold mb-3">Respond</p>
            <div class="card-section p-6">
                @if ($transfer->status !== 'pending')
                    <p class="text-sm" style="color: var(--paper-faint);">
                        This negotiation is {{ $transfer->status }} — no further action can be taken.
                    </p>
                @elseif ($yourTurn)
                    <p class="text-xs mb-4" style="color: var(--paper-faint);">
                        {{ $counterparty?->short_name ?? $counterparty?->name }} is offering
                        <strong style="color: var(--gold);"><x-money :amount="$transfer->fee" :size="13" /></strong> for {{ $transfer->player?->name }}. Accept, counter, or decline.
                    </p>

                    <form method="POST" action="{{ route('manager.negotiations.accept', $transfer) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn-accent w-full py-2.5 text-sm">Accept — Close The Deal</button>
                    </form>

                    <form method="POST" action="{{ route('manager.negotiations.counter', $transfer) }}" class="mb-3 space-y-2">
                        @csrf
                        <input type="text" data-comma-input name="fee" required min="1" placeholder="Counter amount" value="{{ old('fee') }}"
                               class="field px-3 py-2 text-sm w-full">
                        <input type="text" name="message" maxlength="500" placeholder="Optional message" value="{{ old('message') }}"
                               class="field px-3 py-2 text-sm w-full">
                        <button type="submit" class="btn-ghost w-full py-2.5 text-sm">Send Counter-Offer</button>
                    </form>

                    <form method="POST" action="{{ route('manager.negotiations.reject', $transfer) }}">
                        @csrf
                        <button type="submit" class="btn-ghost w-full py-2.5 text-sm" style="color: var(--live); border-color: var(--live);">Decline</button>
                    </form>
                @else
                    <p class="text-sm mb-4" style="color: var(--paper-faint);">
                        Waiting for <strong style="color: var(--paper-dim);">{{ $counterparty?->short_name ?? $counterparty?->name }}</strong> to respond to your offer of
                        <strong style="color: var(--gold);"><x-money :amount="$transfer->fee" :size="13" /></strong>.
                    </p>

                    @if ($youMadeLastOffer)
                        <form method="POST" action="{{ route('manager.negotiations.withdraw', $transfer) }}">
                            @csrf
                            <button type="submit" class="btn-ghost w-full py-2.5 text-sm">Withdraw Offer</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
