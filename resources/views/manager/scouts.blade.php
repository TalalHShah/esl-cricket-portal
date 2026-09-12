@extends('layouts.manager')

@section('title', 'Scouts')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white mb-2 flex items-center gap-2"><span>🔍</span> Scout Free Agents</h1>
        <p class="text-lg text-slate-400">{{ $players->total() }} unsigned player{{ $players->total() !== 1 ? 's' : '' }} available</p>
    </div>

    {{-- Filters --}}
    <div class="card-section rounded mb-8 p-6">
        <form method="GET" action="{{ route('manager.scouts') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Player name..."
                       class="rounded px-3 py-2 text-sm text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Role</label>
                <select name="role" class="rounded px-3 py-2 text-sm text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Tier</label>
                <select name="tier" class="rounded px-3 py-2 text-sm text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    <option value="">All Tiers</option>
                    @foreach ($tiers as $tier)
                        <option value="{{ $tier }}" @selected(request('tier') === $tier)>{{ $tier }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-accent px-4 py-2 text-sm font-bold rounded">Search</button>
            <a href="{{ route('manager.scouts') }}" class="px-4 py-2 text-sm font-semibold text-white rounded" style="background-color: var(--bg-tertiary); border: 1px solid var(--border);">Reset</a>
        </form>
    </div>

    {{-- Players Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($players as $player)
            <div class="card-section rounded p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-black text-white">{{ $player->name }}</h3>
                        <p class="text-xs text-slate-400">{{ $player->country }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-bold rounded" style="background-color: var(--primary); color: white;">{{ $player->tier }}</span>
                </div>
                <div class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Role</span>
                        <span class="text-white font-semibold">{{ $player->role }}</span>
                    </div>
                    @if($player->age)
                        <div class="flex justify-between">
                            <span class="text-slate-400">Age</span>
                            <span class="text-white font-semibold">{{ $player->age }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-400">Value</span>
                        <span class="stat-value text-base">{{ number_format((float) $player->current_value, 0) }}</span>
                    </div>
                </div>
                <a href="{{ route('manager.transfers') }}" class="block w-full text-center btn-accent py-2 text-sm font-bold rounded">
                    View in Transfer Market
                </a>
            </div>
        @empty
            <div class="sm:col-span-2 lg:col-span-3">
                <div class="card-section rounded p-12 text-center text-slate-500">No free agents match your filters</div>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $players->links() }}
    </div>
@endsection
