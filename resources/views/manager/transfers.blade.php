@extends('layouts.manager')

@section('title', 'Transfer Market')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Player Movement</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Transfer Market</h1>
        <p class="text-sm mt-2" style="color: var(--paper-faint);">Make offers for players currently signed with other teams</p>
    </div>

    @if(!$windowOpen)
        <div class="card-section mb-10 p-6" style="border-left: 2px solid var(--live);">
            <p class="eyebrow live mb-1">Transfer Window Closed</p>
            <p class="text-sm" style="color: var(--paper-dim);">Direct offers are suspended while the auction is in progress. Head to the Auction to bid on players instead.</p>
        </div>
    @endif

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
            <div>
                <label class="eyebrow block mb-2">Tier</label>
                <select name="tier" class="field px-3 py-2 text-sm">
                    <option value="">All Tiers</option>
                    @foreach ($tiers as $tier)
                        <option value="{{ $tier }}" @selected(request('tier') === $tier)>{{ $tier }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Team</label>
                <select name="team_id" class="field px-3 py-2 text-sm">
                    <option value="">All Teams</option>
                    @foreach ($teams as $t)
                        <option value="{{ $t->id }}" @selected((string) request('team_id') === (string) $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Country</label>
                <input type="text" name="country" value="{{ request('country') }}" placeholder="e.g. Pakistan" class="field px-3 py-2 text-sm">
            </div>
            <div>
                <label class="eyebrow block mb-2">Sort</label>
                <select name="sort" class="field px-3 py-2 text-sm">
                    <option value="value_desc" @selected($sort === 'value_desc')>Value — High to Low</option>
                    <option value="value_asc" @selected($sort === 'value_asc')>Value — Low to High</option>
                    <option value="name_asc" @selected($sort === 'name_asc')>Name — A to Z</option>
                    <option value="name_desc" @selected($sort === 'name_desc')>Name — Z to A</option>
                    <option value="role" @selected($sort === 'role')>Category</option>
                    <option value="tier" @selected($sort === 'tier')>Tier</option>
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
                    <x-player-card :player="$player" :href="route('manager.players.show', $player)">
                        @if($team && $windowOpen)
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
            <div class="flex items-center justify-between mb-4">
                <p class="eyebrow gold">My Activity</p>
                <a href="{{ route('manager.negotiations.index') }}" class="text-xs underline" style="color: var(--paper-faint);">View All Negotiations</a>
            </div>
            <div class="card-section">
                @forelse ($myTransfers as $transfer)
                    <a href="{{ route('manager.negotiations.show', $transfer) }}" class="news-row px-6" style="display:block; text-decoration:none;">
                        <p class="font-semibold text-sm" style="color: var(--paper);">{{ $transfer->player?->name }}</p>
                        <p class="text-xs mb-3" style="color: var(--paper-faint);">
                            {{ $transfer->fromTeam?->short_name ?? '—' }} &rarr; {{ $transfer->toTeam?->short_name ?? '—' }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold" style="color: var(--gold);"><x-money :amount="$transfer->fee" /></span>
                            @if ($transfer->status === 'pending')
                                <span class="status-pill {{ $transfer->awaitingResponseFrom($team?->id) ? 'live' : 'pending' }}">
                                    {{ $transfer->awaitingResponseFrom($team?->id) ? 'Your Move' : 'Awaiting Them' }}
                                </span>
                            @else
                                <span class="status-pill {{ $transfer->status === 'approved' ? 'confirmed' : '' }}">{{ ucfirst($transfer->status) }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="p-10 text-center" style="color: var(--paper-faint);">No transfer activity yet</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
