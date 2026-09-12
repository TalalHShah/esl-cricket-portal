@extends('layouts.manager')

@section('title', 'Transfer Market')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Player Movement</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Transfer Market</h1>
        <p class="text-sm mt-2" style="color: var(--paper-faint);">Make offers for players currently signed with other teams</p>
    </div>

    @if($team)
        <div class="stat mb-10">
            <p class="stat-figure up"><x-money :amount="$team->remainingBudget()" :size="18" /></p>
            <p class="stat-caption">Available Budget</p>
        </div>
    @endif

    <div class="card-section mb-10 p-6">
        <form method="GET" action="{{ route('manager.transfers') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="eyebrow block mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Player name" class="field px-3 py-2 text-sm">
            </div>
            <div>
                <label class="eyebrow block mb-2">Role</label>
                <select name="role" class="field px-3 py-2 text-sm">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-accent px-5 py-2.5">Search</button>
            <a href="{{ route('manager.transfers') }}" class="btn-ghost px-5 py-2.5">Reset</a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @forelse ($listedPlayers as $player)
                    <x-player-card :player="$player">
                        @if($team)
                            <form method="POST" action="{{ route('manager.transfers.offer', $player) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="number" name="fee" required min="1" placeholder="Offer amount"
                                       class="field px-3 py-2 text-xs flex-1" style="background: rgba(0,0,0,0.35); border-color: rgba(255,255,255,0.4); color: #fff;">
                                <button type="submit" class="px-4 py-2 text-xs font-semibold uppercase tracking-wide whitespace-nowrap"
                                        style="background: var(--gold); color: var(--ink); border-radius: 2px;">
                                    Offer
                                </button>
                            </form>
                        @endif
                    </x-player-card>
                @empty
                    <div class="sm:col-span-2">
                        <div class="card-section p-12 text-center" style="color: var(--paper-faint);">No players available in the market</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $listedPlayers->links() }}
            </div>
        </div>

        <div>
            <p class="eyebrow gold mb-4">My Activity</p>
            <div class="card-section">
                @forelse ($myTransfers as $transfer)
                    <div class="news-row px-6">
                        <p class="font-semibold text-sm" style="color: var(--paper);">{{ $transfer->player?->name }}</p>
                        <p class="text-xs mb-3" style="color: var(--paper-faint);">
                            {{ $transfer->fromTeam?->short_name ?? '—' }} &rarr; {{ $transfer->toTeam?->short_name ?? '—' }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold" style="color: var(--gold);"><x-money :amount="$transfer->fee" /></span>
                            <span class="status-pill {{ $transfer->status === 'approved' ? 'confirmed' : ($transfer->status === 'rejected' ? 'live' : 'pending') }}">
                                {{ ucfirst($transfer->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center" style="color: var(--paper-faint);">No transfer activity yet</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
