@extends('layouts.admin')

@section('title', 'New Auction')

@section('content')
    @include('partials.page-header', ['title' => 'Queue a Player For Auction', 'eyebrow' => 'Auction'])

    <div class="card-section p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="eyebrow block mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Player name..." class="field w-full px-4 py-2.5">
            </div>
            <div>
                <label class="eyebrow block mb-2">Tier</label>
                <select name="tier" class="field w-full px-4 py-2.5" onchange="this.form.submit()">
                    <option value="">All Tiers</option>
                    @foreach ($tiers as $tier)
                        <option value="{{ $tier }}" @selected(request('tier') === $tier)>{{ $tier }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Category</label>
                <select name="role" class="field w-full px-4 py-2.5" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-3">
                <label class="eyebrow block mb-2">Country</label>
                <input type="text" name="country" value="{{ request('country') }}" placeholder="e.g. Pakistan" class="field w-full px-4 py-2.5">
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="btn-accent px-6 py-2.5 flex-1">Filter</button>
                <a href="{{ route('admin.auctions.create') }}" class="btn-ghost px-6 py-2.5 flex-1 text-center">Reset</a>
            </div>
        </form>
    </div>

    <p class="text-xs mb-4" style="color: var(--paper-faint);">{{ $players->total() }} free agent(s) not already in the draft pool or another auction.</p>

    <div class="card-section overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Category</th>
                    <th>Tier</th>
                    <th style="text-align:right;">Current Value</th>
                    <th style="text-align:right;">Starting Bid / Increment</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    @php
                        $defaultBid = (float) $player->current_value ?: 500000;
                        $defaultIncrement = $defaultBid < 1000000 ? 50000 : 100000;
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.players.edit', $player) }}" class="text-link font-semibold">{{ $player->name }}</a>
                            <p class="text-xs mt-1" style="color: var(--paper-faint);">{{ $player->country }}</p>
                        </td>
                        <td style="color: var(--paper-dim);">{{ $player->typeLabel() }}</td>
                        <td><span class="tag gold">{{ $player->tier }}</span></td>
                        <td style="text-align:right; color: var(--gold); font-weight: 600;"><x-money :amount="$player->current_value" /></td>
                        <td>
                            <form action="{{ route('admin.auctions.store') }}" method="POST" class="flex items-center justify-end gap-2">
                                @csrf
                                <input type="hidden" name="player_id" value="{{ $player->id }}">
                                <input type="number" name="starting_bid" value="{{ $defaultBid }}" min="1" class="field px-2 py-1.5 text-sm" style="width: 110px;" title="Starting bid">
                                <input type="number" name="bid_increment" value="{{ $defaultIncrement }}" min="1" class="field px-2 py-1.5 text-sm" style="width: 90px;" title="Bid increment">
                                <button type="submit" class="btn-accent px-3 py-1.5 text-xs whitespace-nowrap">Queue</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No free agents match this filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $players->links() }}</div>

    @error('player_id') <p class="text-sm mt-4" style="color: var(--live);">{{ $message }}</p> @enderror
@endsection
