@extends('layouts.manager')

@section('title', 'Team Profile')

@section('content')
    @if(!$team)
        <div class="card-section rounded p-12 text-center text-slate-500">You are not assigned to manage a team.</div>
    @else
        <div class="featured-story rounded mb-8">
            <div class="flex items-start gap-8">
                @if($team->logo)
                    <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}"
                         class="w-20 h-20 rounded object-cover flex-shrink-0" style="border: 3px solid var(--accent);">
                @else
                    <div class="team-badge-placeholder text-2xl flex-shrink-0" style="background-color: var(--primary); border-color: var(--accent); width: 80px; height: 80px;">
                        {{ strtoupper(substr($team->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <h1 class="text-4xl font-black text-white mb-2">{{ $team->name }}</h1>
                    <p class="text-lg text-blue-100 mb-2">{{ $team->short_name }}</p>
                    @if($team->description)
                        <p class="text-blue-100">{{ $team->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="stat-box">
                <div class="stat-value">{{ number_format((float) $team->budget, 0) }}</div>
                <div class="stat-label">Total Budget</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ number_format((float) $team->spent, 0) }}</div>
                <div class="stat-label">Spent</div>
            </div>
            <div class="stat-box">
                <div class="stat-value" style="color: var(--success);">{{ number_format($team->remainingBudget(), 0) }}</div>
                <div class="stat-label">Remaining</div>
            </div>
        </div>

        <div class="card-section rounded">
            <div class="card-header flex items-center gap-2">
                <span>👥</span>
                <h2>Full Squad ({{ $players->count() }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="background-color: var(--bg-tertiary); border-bottom: 1px solid var(--border);">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold text-slate-300 uppercase text-xs">Player</th>
                            <th class="px-6 py-3 text-left font-bold text-slate-300 uppercase text-xs">Role</th>
                            <th class="px-6 py-3 text-left font-bold text-slate-300 uppercase text-xs">Tier</th>
                            <th class="px-6 py-3 text-left font-bold text-slate-300 uppercase text-xs">Age</th>
                            <th class="px-6 py-3 text-right font-bold text-slate-300 uppercase text-xs">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($players as $player)
                            <tr class="border-b hover:opacity-80 transition" style="border-color: var(--border);">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-white">{{ $player->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $player->country }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-400">{{ $player->role }}</td>
                                <td class="px-6 py-4 text-slate-400">{{ $player->tier }}</td>
                                <td class="px-6 py-4 text-slate-400">{{ $player->age ?? '—' }}</td>
                                <td class="px-6 py-4 text-right stat-value">{{ number_format((float) $player->current_value, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No players in squad yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
