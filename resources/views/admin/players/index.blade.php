@extends('layouts.admin')

@section('title', 'Manage Players')

@section('content')
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-4xl font-black text-white">Players</h1>
            <p class="mt-2 text-slate-400">{{ $players->total() }} players in the league</p>
        </div>
        <div class="flex items-center gap-3">
            @include('partials.sort-bar', ['current' => $sort, 'sortId' => 'players', 'options' => [
                'name_asc' => 'Name — A to Z',
                'name_desc' => 'Name — Z to A',
                'value_desc' => 'Value — High to Low',
                'value_asc' => 'Value — Low to High',
                'tier' => 'Tier',
                'role' => 'Category',
                'status' => 'Status',
            ]])
            <a href="{{ route('admin.players.create') }}" class="px-5 py-3 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold transition whitespace-nowrap">
                + Add Player
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="cricket-card rounded-2xl overflow-x-auto">
        <table class="w-full">
            <thead class="bg-amber-500/10 border-b border-amber-500/20">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Name</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Team</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Role</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Tier</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Base Value</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-amber-400">Status</th>
                    <th class="px-6 py-4 text-right text-xs font-bold uppercase text-amber-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse ($players as $player)
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4 font-semibold text-white">{{ $player->name }}</td>
                        <td class="px-6 py-4 text-slate-400 text-sm">{{ $player->team?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-slate-400 text-sm">{{ $player->role }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ match($player->tier) {
                                'Superstar' => 'bg-purple-500/20 text-purple-300',
                                'Star' => 'bg-amber-500/20 text-amber-300',
                                'Normal' => 'bg-blue-500/20 text-blue-300',
                                'Low-value' => 'bg-slate-500/20 text-slate-300',
                            } }}">
                                {{ $player->tier }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-emerald-400 font-semibold"><x-money :amount="$player->base_value" :size="14" /></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $player->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                                {{ $player->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.players.edit', $player) }}" class="px-3 py-1 rounded text-sm font-bold bg-amber-600/20 text-amber-300 hover:bg-amber-600/30 transition inline-block">
                                Edit
                            </a>
                            <form action="{{ route('admin.players.destroy', $player) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this player?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 rounded text-sm font-bold bg-red-600/20 text-red-300 hover:bg-red-600/30 transition">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No players found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $players->links() }}
    </div>
@endsection
