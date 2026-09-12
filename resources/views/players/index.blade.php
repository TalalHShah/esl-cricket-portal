@extends('layouts.app')

@section('title', 'Players')

@section('content')
    @include('partials.page-header', [
        'title' => 'Players',
        'eyebrow' => 'The League',
        'subtitle' => $players->total() . ' player' . ($players->total() !== 1 ? 's' : '') . ' registered',
    ])

    <div class="card-section mb-10 p-6">
        <form method="GET" action="{{ route('players.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="eyebrow block mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Player name" class="field w-full px-3 py-2 text-sm">
            </div>
            <div>
                <label class="eyebrow block mb-2">Role</label>
                <select name="role" class="field px-3 py-2 text-sm">
                    <option value="">All Roles</option>
                    @foreach (['Batsman', 'All-rounder', 'Bowler', 'Wicketkeeper'] as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Tier</label>
                <select name="tier" class="field px-3 py-2 text-sm">
                    <option value="">All Tiers</option>
                    @foreach (['Superstar', 'Star', 'Normal', 'Low-value'] as $tier)
                        <option value="{{ $tier }}" @selected(request('tier') === $tier)>{{ $tier }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Team</label>
                <select name="team" class="field px-3 py-2 text-sm">
                    <option value="">All Teams</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" @selected((string) request('team') === (string) $team->id)>{{ $team->name }}</option>
                    @endforeach
                </select>
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
                    <option value="age_asc" @selected($sort === 'age_asc')>Age — Youngest First</option>
                    <option value="age_desc" @selected($sort === 'age_desc')>Age — Oldest First</option>
                    <option value="country_asc" @selected($sort === 'country_asc')>Country</option>
                </select>
            </div>
            <button type="submit" class="btn-accent px-5 py-2.5">Filter</button>
            <a href="{{ route('players.index') }}" class="btn-ghost px-5 py-2.5">Reset</a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($players as $player)
            <x-player-card :player="$player" :href="route('players.show', $player)" />
        @empty
            <div class="sm:col-span-2 lg:col-span-4">
                @include('partials.empty-state', ['message' => 'No players match the current filters.'])
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $players->links() }}
    </div>
@endsection
