@extends('layouts.manager')

@section('title', 'Scouts')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Player Recruitment</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Scout Players</h1>
        <p class="text-sm mt-2" style="color: var(--paper-faint);">{{ $players->total() }} player{{ $players->total() !== 1 ? 's' : '' }} across the league — everyone outside your own squad</p>
    </div>

    @if(!$windowOpen)
        <div class="card-section mb-10 p-6" style="border-left: 2px solid var(--live);">
            <p class="eyebrow live mb-1">Transfer Window Closed</p>
            <p class="text-sm" style="color: var(--paper-dim);">Free agent signing and direct offers are suspended while the auction is in progress. Head to the Auction to bid on players instead.</p>
        </div>
    @endif

    <div class="card-section mb-10 p-6">
        <form method="GET" action="{{ route('manager.scouts') }}" class="flex flex-wrap items-end gap-3">
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
                <label class="eyebrow block mb-2">Availability</label>
                <select name="availability" class="field px-3 py-2 text-sm">
                    <option value="">All Players</option>
                    <option value="free" @selected(request('availability') === 'free')>Free Agents Only</option>
                    <option value="signed" @selected(request('availability') === 'signed')>Signed Players Only</option>
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Sort</label>
                <select name="sort" class="field px-3 py-2 text-sm">
                    <option value="default" @selected($sort === 'default')>Free Agents First</option>
                    <option value="value_desc" @selected($sort === 'value_desc')>Value — High to Low</option>
                    <option value="value_asc" @selected($sort === 'value_asc')>Value — Low to High</option>
                    <option value="name_asc" @selected($sort === 'name_asc')>Name — A to Z</option>
                    <option value="name_desc" @selected($sort === 'name_desc')>Name — Z to A</option>
                    <option value="role" @selected($sort === 'role')>Category</option>
                    <option value="tier" @selected($sort === 'tier')>Tier</option>
                    <option value="age_asc" @selected($sort === 'age_asc')>Age — Youngest First</option>
                    <option value="age_desc" @selected($sort === 'age_desc')>Age — Oldest First</option>
                    <option value="country_asc" @selected($sort === 'country_asc')>Country</option>
                </select>
            </div>
            <button type="submit" class="btn-accent px-5 py-2.5">Search</button>
            <a href="{{ route('manager.scouts') }}" class="btn-ghost px-5 py-2.5">Reset</a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($players as $player)
            <x-player-card :player="$player" :href="route('manager.players.show', $player)">
                @if(auth()->user()->managedTeam && $windowOpen)
                    @if($player->team)
                        <a href="{{ route('manager.transfers', ['search' => $player->name]) }}"
                           class="block w-full text-center py-2 text-xs font-semibold uppercase tracking-wide"
                           style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.4); border-radius: 2px;">
                            Make Offer
                        </a>
                    @else
                        <form method="POST" action="{{ route('manager.scouts.sign', $player) }}">
                            @csrf
                            <button type="submit" class="btn-accent w-full py-2 text-xs">Sign Player</button>
                        </form>
                    @endif
                @endif
            </x-player-card>
        @empty
            <div class="sm:col-span-2 lg:col-span-4">
                <div class="card-section p-12 text-center" style="color: var(--paper-faint);">No players match your filters</div>
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $players->links() }}
    </div>
@endsection
