@extends('layouts.app')

@section('title', 'Managers')

@section('content')
    @include('partials.page-header', [
        'title' => 'Managers',
        'eyebrow' => 'The League',
        'subtitle' => $teams->count() . ' team' . ($teams->count() !== 1 ? 's' : '') . ' — meet the managers behind them',
    ])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($teams as $team)
            <a href="{{ route('teams.show', $team) }}" class="card-section lift-on-hover p-6">
                <div class="flex items-center gap-4">
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
                        <p class="font-display text-lg font-semibold" style="color: var(--paper);">{{ $team->manager?->name ?? 'Unassigned' }}</p>
                        <p class="text-xs mt-1" style="color: var(--paper-faint);">{{ $team->name }} &middot; {{ $team->players_count }} player{{ $team->players_count !== 1 ? 's' : '' }}</p>
                    </div>
                </div>
            </a>
        @empty
            @include('partials.empty-state', ['message' => 'No teams have been created yet.'])
        @endforelse
    </div>
@endsection
