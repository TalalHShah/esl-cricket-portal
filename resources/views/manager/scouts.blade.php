@extends('layouts.manager')

@section('title', 'Scouts')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Player Recruitment</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Scout Free Agents</h1>
        <p class="text-sm mt-2" style="color: var(--paper-faint);">{{ $players->total() }} unsigned player{{ $players->total() !== 1 ? 's' : '' }} available</p>
    </div>

    <div class="card-section mb-10 p-6">
        <form method="GET" action="{{ route('manager.scouts') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="eyebrow block mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Player name"
                       class="field px-3 py-2 text-sm">
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
            <button type="submit" class="btn-accent px-5 py-2.5">Search</button>
            <a href="{{ route('manager.scouts') }}" class="btn-ghost px-5 py-2.5">Reset</a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($players as $player)
            <div class="card-section lift-on-hover p-4">
                <div class="player-portrait mb-4" style="aspect-ratio: 3/4;">
                    @if($player->image)
                        <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                    @else
                        <div class="initials">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                    @endif
                </div>
                <div class="flex items-start justify-between mb-3">
                    <h3 class="text-base font-semibold" style="color: var(--paper);">{{ $player->name }}</h3>
                    <span class="tag gold">{{ $player->tier }}</span>
                </div>
                <p class="text-xs mb-1" style="color: var(--paper-faint);">{{ $player->role }} — {{ $player->country }}</p>
                @if($player->age)
                    <p class="text-xs mb-3" style="color: var(--paper-faint);">Age {{ $player->age }}</p>
                @endif
                <p class="text-lg font-semibold mb-4" style="color: var(--gold);">{{ number_format((float) $player->current_value, 0) }}</p>
                <a href="{{ route('manager.transfers') }}" class="btn-ghost w-full py-2 text-xs">View in Transfer Market</a>
            </div>
        @empty
            <div class="sm:col-span-2 lg:col-span-4">
                <div class="card-section p-12 text-center" style="color: var(--paper-faint);">No free agents match your filters</div>
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $players->links() }}
    </div>
@endsection
