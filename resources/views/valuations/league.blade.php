@extends('layouts.app')

@section('title', 'League Valuations')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white">💰 League Valuations</h1>
        <p class="mt-2 text-slate-400">Current market values across all active players</p>
    </div>

    {{-- Quick Links --}}
    <div class="flex gap-3 mb-8">
        <a href="{{ route('valuations.league') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold text-sm">
            📊 League View
        </a>
        <a href="{{ route('valuations.tiers') }}" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-sm transition">
            ⭐ By Tier
        </a>
    </div>

    {{-- Players Table --}}
    <div class="cricket-card rounded-2xl overflow-x-auto">
        <table class="w-full">
            <thead class="bg-emerald-500/10 border-b border-emerald-500/20">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-emerald-400">Rank</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-emerald-400">Player</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-emerald-400">Team</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase text-emerald-400">Tier</th>
                    <th class="px-6 py-4 text-right text-xs font-bold uppercase text-emerald-400">Base Value</th>
                    <th class="px-6 py-4 text-right text-xs font-bold uppercase text-emerald-400">Current Value</th>
                    <th class="px-6 py-4 text-right text-xs font-bold uppercase text-emerald-400">Change</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse ($players as $index => $player)
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4 font-bold text-white">{{ $loop->iteration + ($players->currentPage() - 1) * $players->perPage() }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('players.show', $player) }}" class="font-semibold text-emerald-400 hover:text-emerald-300">
                                {{ $player->name }}
                            </a>
                            <p class="text-xs text-slate-500">{{ $player->role }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-sm">
                            @if ($player->team)
                                <a href="{{ route('teams.show', $player->team) }}" class="hover:text-white transition">
                                    {{ $player->team->name }}
                                </a>
                            @else
                                <span class="text-amber-400">🔓 Free Agent</span>
                            @endif
                        </td>
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
                        <td class="px-6 py-4 text-right text-slate-400">PKR {{ number_format($player->base_value, 0) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-400">PKR {{ number_format($player->current_value, 0) }}</td>
                        <td class="px-6 py-4 text-right font-bold">
                            @php
                                $change = $player->current_value - $player->base_value;
                                $changePercent = ($change / $player->base_value) * 100;
                            @endphp
                            @if ($change > 0)
                                <span class="text-emerald-400">
                                    +{{ number_format($change, 0) }} (+{{ number_format($changePercent, 1) }}%)
                                </span>
                            @elseif ($change < 0)
                                <span class="text-red-400">
                                    {{ number_format($change, 0) }} ({{ number_format($changePercent, 1) }}%)
                                </span>
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
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
