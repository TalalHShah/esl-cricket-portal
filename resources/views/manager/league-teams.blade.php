@extends('layouts.manager')

@section('title', 'League Teams')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">The League</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Teams</h1>
        <p class="text-sm mt-2" style="color: var(--paper-faint);">Scout the competition — view any team's squad and budget</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($teams as $team)
            @php $isOwn = $ownTeam && $ownTeam->id === $team->id; @endphp
            <a href="{{ route('manager.teams.show', $team) }}" class="card-section lift-on-hover p-6" style="{{ $isOwn ? 'border-color: var(--gold);' : '' }}">
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
                    @if($isOwn)
                        <span class="tag gold">Your Team</span>
                    @endif
                </div>

                <div class="grid grid-cols-3 gap-3 pt-4" style="border-top: var(--rule);">
                    <div>
                        <p class="stat-caption mb-1">Players</p>
                        <p class="text-base font-semibold" style="color: var(--paper);">{{ $team->players_count }}</p>
                    </div>
                    <div>
                        <p class="stat-caption mb-1">Budget</p>
                        <p class="text-base font-semibold" style="color: var(--gold);"><x-money :amount="$team->budget" /></p>
                    </div>
                    <div>
                        <p class="stat-caption mb-1">Remaining</p>
                        <p class="text-base font-semibold" style="color: var(--up);"><x-money :amount="$team->remainingBudget()" /></p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endsection
