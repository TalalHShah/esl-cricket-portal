@extends('layouts.app')

@section('title', $team->name)

@section('content')
    {{-- Team Hero Section --}}
    <div class="featured-story rounded mb-8">
        <div class="flex items-start gap-8">
            @if($team->logo)
                <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}"
                     class="w-20 h-20 rounded object-cover flex-shrink-0" style="border: 3px solid var(--accent);">
            @else
                <div class="team-badge-placeholder text-2xl" style="background-color: var(--primary); border-color: var(--accent); width: 80px; height: 80px;">
                    {{ $team->short_name ?? strtoupper(substr($team->name, 0, 1)) }}
                </div>
            @endif
            <div class="flex-1">
                <h1 class="text-4xl font-black text-white mb-2">{{ $team->name }}</h1>
                <p class="text-lg text-blue-100 mb-4">
                    @if($team->manager)
                        Managed by <span class="font-bold">{{ $team->manager->name }}</span>
                    @else
                        Unassigned Manager
                    @endif
                    @if($team->short_name)
                        • <span class="font-bold">{{ $team->short_name }}</span>
                    @endif
                </p>
                @if($team->description)
                    <p class="text-blue-100">{{ $team->description }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Budget Stats --}}
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
            <div class="stat-value">{{ number_format($team->remainingBudget(), 0) }}</div>
            <div class="stat-label">Remaining</div>
        </div>
    </div>

    {{-- Squad and Recent Matches --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Squad Table --}}
        <div class="lg:col-span-2">
            <div class="card-section rounded">
                <div class="card-header flex items-center gap-2">
                    <span>👥</span>
                    <h2>Squad <span class="text-sm text-slate-400 font-normal">({{ $players->count() }} players)</span></h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead style="background-color: var(--bg-tertiary); border-bottom: 1px solid var(--border);">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-slate-300 uppercase text-xs">Player</th>
                                <th class="px-6 py-3 text-left font-bold text-slate-300 uppercase text-xs">Role</th>
                                <th class="px-6 py-3 text-left font-bold text-slate-300 uppercase text-xs">Tier</th>
                                <th class="px-6 py-3 text-right font-bold text-slate-300 uppercase text-xs">Value</th>
                            </tr>
                        </thead>
                        <tbody style="border-color: var(--border);">
                            @forelse ($players as $player)
                                <tr class="border-b hover:opacity-80 transition" style="border-color: var(--border);">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-semibold text-white">{{ $player->name }}</p>
                                            <p class="text-xs text-slate-400">{{ $player->country }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-400">{{ $player->role }}</td>
                                    <td class="px-6 py-4 text-slate-400">{{ $player->tier }}</td>
                                    <td class="px-6 py-4 text-right stat-value">{{ number_format((float) $player->current_value, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">No players in this squad yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Matches Sidebar --}}
        <div>
            <div class="card-section rounded">
                <div class="card-header flex items-center gap-2">
                    <span>🏟️</span>
                    <h2>Recent Matches</h2>
                </div>
                <div class="divide-y" style="border-color: var(--border);">
                    @forelse ($matches as $match)
                        <a href="{{ route('matches.show', $match) }}" class="block p-6 hover:opacity-80 transition border-l-4" style="border-left-color: var(--accent);">
                            <p class="text-sm text-slate-400 mb-1">{{ optional($match->match_date)->format('d M Y') }}</p>
                            <h3 class="font-bold text-white text-lg">
                                {{ $match->homeTeam?->short_name ?? $match->homeTeam?->name ?? 'TBD' }}
                                <span class="text-slate-500 font-normal text-sm">vs</span>
                                {{ $match->awayTeam?->short_name ?? $match->awayTeam?->name ?? 'TBD' }}
                            </h3>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs text-slate-500">{{ $match->venue ?? 'TBD' }}</span>
                                <span class="px-2 py-1 text-xs font-bold text-white rounded" style="background-color: var(--primary);">
                                    {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center text-slate-500">No matches played yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
