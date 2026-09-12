@extends('layouts.app')

@section('title', 'Teams')

@section('content')
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white mb-2 flex items-center gap-2">
            <span>🏏</span> Teams
        </h1>
        <p class="text-lg text-slate-400">{{ $teams->total() }} team{{ $teams->total() !== 1 ? 's' : '' }} in the Elite Series League</p>
    </div>

    {{-- Teams Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($teams as $team)
            <a href="{{ route('teams.show', $team) }}" class="card-section rounded overflow-hidden hover:shadow-lg transition">
                <div class="p-6">
                    {{-- Team Header --}}
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4 flex-1">
                            @if($team->logo)
                                <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}"
                                     class="w-14 h-14 rounded object-cover flex-shrink-0" style="border: 2px solid var(--accent);">
                            @else
                                <div class="team-badge-placeholder flex-shrink-0" style="background-color: var(--primary); border-color: var(--accent);">
                                    {{ $team->short_name ?? strtoupper(substr($team->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-black text-white">{{ $team->name }}</h3>
                                <p class="text-xs text-slate-400 mt-1">{{ $team->manager?->name ?? 'No manager' }}</p>
                            </div>
                        </div>
                        @if(!$team->is_active)
                            <span class="text-xs font-bold uppercase px-2 py-1 rounded whitespace-nowrap" style="color: var(--text-muted);">Inactive</span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if($team->description)
                        <p class="text-sm text-slate-400 mb-4 line-clamp-2">{{ $team->description }}</p>
                    @endif

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-3 gap-3 border-t pt-4" style="border-color: var(--border);">
                        <div class="text-center">
                            <p class="text-xs font-bold uppercase text-slate-400 mb-1">Players</p>
                            <p class="text-lg font-black text-white">{{ $team->players_count ?? 0 }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-bold uppercase text-slate-400 mb-1">Budget</p>
                            <p class="stat-value text-sm">{{ number_format((float) $team->budget, 0) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-bold uppercase text-slate-400 mb-1">Remaining</p>
                            <p class="text-lg font-black" style="color: var(--accent);">{{ number_format($team->remainingBudget(), 0) }}</p>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="md:col-span-2 lg:col-span-3">
                <div class="card-section rounded p-12 text-center">
                    <p class="text-lg text-slate-500">No teams have been created yet</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($teams->hasPages())
        <div class="mt-8">
            {{ $teams->links() }}
        </div>
    @endif
@endsection
