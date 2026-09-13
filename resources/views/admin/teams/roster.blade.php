@extends('layouts.admin')

@section('title', $team->name . ' Roster')

@section('content')
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('admin.teams.index') }}" class="text-sm text-slate-400 hover:text-slate-200 transition">&larr; All Teams</a>
            <h1 class="text-4xl font-black text-white mt-2">{{ $team->name }} — Roster</h1>
            <p class="mt-2 text-slate-400">{{ $players->count() }} / {{ \App\Models\Team::SQUAD_LIMIT }} players</p>
        </div>
        @if ($players->where('is_manager_player', false)->isNotEmpty())
            <form action="{{ route('admin.teams.players.remove-all', $team) }}" method="POST"
                  onsubmit="return confirm('Remove every non-manager player from {{ $team->name }}\'s roster? Their spent budget will be refunded. This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-5 py-3 rounded-lg bg-red-600/20 hover:bg-red-600/30 text-red-300 font-bold transition border border-red-500/30">
                    Clear Entire Roster
                </button>
            </form>
        @endif
    </div>

    @error('roster')
        <div class="mb-6 rounded-lg bg-red-500/10 border border-red-500/30 px-4 py-3 text-red-300 text-sm">{{ $message }}</div>
    @enderror

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-8">
        <div class="cricket-card rounded-2xl p-6">
            <p class="text-xs font-bold uppercase text-emerald-400">Budget</p>
            <p class="text-2xl font-black text-white mt-2"><x-money :amount="$team->budget" :size="16" /></p>
        </div>
        <div class="cricket-card rounded-2xl p-6">
            <p class="text-xs font-bold uppercase text-amber-400">Spent</p>
            <p class="text-2xl font-black text-white mt-2"><x-money :amount="$team->spent" :size="16" /></p>
        </div>
        <div class="cricket-card rounded-2xl p-6">
            <p class="text-xs font-bold uppercase text-blue-400">Remaining</p>
            <p class="text-2xl font-black text-white mt-2"><x-money :amount="$team->remainingBudget()" :size="16" /></p>
        </div>
    </div>

    <div class="cricket-card rounded-2xl overflow-x-auto">
        <table class="w-full text-left" style="min-width: 640px;">
            <thead>
                <tr class="border-b border-slate-800">
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Player</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Category</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Tier</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-right">Sold Price</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    <tr class="border-b border-slate-800/60">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.players.edit', $player) }}" class="font-bold text-white hover:text-amber-300 transition">{{ $player->name }}</a>
                            <p class="text-xs text-slate-500">{{ $player->country }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ $player->typeLabel() }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-bold {{ $player->is_manager_player ? 'bg-amber-600/20 text-amber-300' : 'bg-slate-800 text-slate-300' }}">
                                {{ $player->is_manager_player ? 'Manager' : $player->tier }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-slate-300"><x-money :amount="$player->sold_price ?? 0" :size="13" /></td>
                        <td class="px-6 py-4 text-right">
                            @unless ($player->is_manager_player)
                                <form action="{{ route('admin.teams.players.remove', [$team, $player]) }}" method="POST"
                                      onsubmit="return confirm('Remove {{ $player->name }} from {{ $team->name }}? Their sold price will be refunded to the team\'s budget.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded text-xs font-bold bg-red-600/20 text-red-300 hover:bg-red-600/30 transition">
                                        Remove &amp; Refund
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-600">—</span>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">No players on this roster yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
