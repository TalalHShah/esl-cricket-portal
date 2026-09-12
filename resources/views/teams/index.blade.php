@extends('layouts.app')

@section('title', 'Teams')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">The League</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Teams</h1>
        <p class="text-sm mt-2" style="color: var(--paper-faint);">{{ $teams->total() }} team{{ $teams->total() !== 1 ? 's' : '' }} competing this season</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($teams as $team)
            <a href="{{ route('teams.show', $team) }}" class="card-section lift-on-hover p-6">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4 flex-1">
                        @if($team->logo)
                            <div class="crest" style="width:56px;height:56px;">
                                <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="crest" style="width:56px;height:56px; font-size: 1.2rem;">
                                {{ $team->short_name ?? strtoupper(substr($team->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h3 class="font-display text-lg font-semibold" style="color: var(--paper);">{{ $team->name }}</h3>
                            <p class="text-xs mt-1" style="color: var(--paper-faint);">{{ $team->manager?->name ?? 'No manager' }}</p>
                        </div>
                    </div>
                    @if(!$team->is_active)
                        <span class="eyebrow whitespace-nowrap">Inactive</span>
                    @endif
                </div>

                @if($team->description)
                    <p class="text-sm mb-4 line-clamp-2" style="color: var(--paper-faint);">{{ $team->description }}</p>
                @endif

                <div class="grid grid-cols-3 gap-3 pt-4" style="border-top: var(--rule);">
                    <div>
                        <p class="stat-caption mb-1">Players</p>
                        <p class="text-base font-semibold" style="color: var(--paper);">{{ $team->players_count ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="stat-caption mb-1">Budget</p>
                        <p class="text-base font-semibold" style="color: var(--gold);">{{ number_format((float) $team->budget, 0) }}</p>
                    </div>
                    <div>
                        <p class="stat-caption mb-1">Remaining</p>
                        <p class="text-base font-semibold" style="color: var(--up);">{{ number_format($team->remainingBudget(), 0) }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="md:col-span-2 lg:col-span-3">
                <div class="card-section p-12 text-center" style="color: var(--paper-faint);">No teams have been created yet</div>
            </div>
        @endforelse
    </div>

    @if($teams->hasPages())
        <div class="mt-10">
            {{ $teams->links() }}
        </div>
    @endif
@endsection
