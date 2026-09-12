@extends('layouts.app')

@section('title', 'Players')

@section('content')
    @include('partials.page-header', [
        'title' => 'Players',
        'subtitle' => $players->total() . ' player(s) registered.',
    ])

    <form method="GET" action="{{ route('players.index') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-800 bg-slate-900/60 p-4">
        <div class="flex-1 min-w-[180px]">
            <label class="mb-1 block text-xs uppercase text-slate-500">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Player name..."
                   class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white placeholder-slate-600 focus:border-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Role</label>
            <select name="role" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                <option value="">All roles</option>
                @foreach (['Batsman', 'Wicketkeeper', 'All-rounder', 'Fast Bowler', 'Spinner'] as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Tier</label>
            <select name="tier" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                <option value="">All tiers</option>
                @foreach (['Superstar', 'Star', 'Normal', 'Low-value'] as $tier)
                    <option value="{{ $tier }}" @selected(request('tier') === $tier)>{{ $tier }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs uppercase text-slate-500">Team</label>
            <select name="team" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none">
                <option value="">All teams</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((string) request('team') === (string) $team->id)>{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">
            Filter
        </button>
        <a href="{{ route('players.index') }}" class="rounded-md border border-slate-700 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Reset</a>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Player</th>
                        <th class="px-5 py-3">Country</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Tier</th>
                        <th class="px-5 py-3">Team</th>
                        <th class="px-5 py-3 text-right">Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($players as $player)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-5 py-3">
                                <a href="{{ route('players.show', $player) }}" class="font-medium text-white hover:text-emerald-400">
                                    {{ $player->name }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-slate-400">{{ $player->country }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $player->role }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $player->tier }}</td>
                            <td class="px-5 py-3">
                                @if ($player->team)
                                    <a href="{{ route('teams.show', $player->team) }}" class="text-sky-400 hover:text-sky-300">
                                        {{ $player->team->name }}
                                    </a>
                                @else
                                    <span class="text-slate-500">Free Agent</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-400">
                                {{ number_format((float) $player->current_value, 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">No players match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $players->links() }}
    </div>
@endsection
