@extends('layouts.manager')

@section('title', $team->name)

@section('content')
    @php $isOwn = $ownTeam && $ownTeam->id === $team->id; @endphp

    <div class="masthead mb-10">
        <div class="flex items-start gap-8">
            @if($team->logo)
                <div class="crest" style="width:88px;height:88px;">
                    <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="crest" style="width:88px;height:88px; font-size: 2rem;">
                    {{ $team->short_name ?? strtoupper(substr($team->name, 0, 1)) }}
                </div>
            @endif
            <div class="flex-1">
                <p class="eyebrow gold mb-2">
                    {{ $team->manager ? 'Managed by ' . $team->manager->name : 'Unmanaged' }}
                    @if($isOwn) — Your Team @endif
                </p>
                <h1 class="font-display text-4xl md:text-5xl font-semibold mb-3" style="color: var(--paper);">{{ $team->name }}</h1>
                @if($team->description)
                    <p class="text-lg" style="color: var(--paper-dim); max-width: 46rem;">{{ $team->description }}</p>
                @endif
            </div>
            <a href="{{ route('manager.teams.index') }}" class="btn-ghost px-4 py-2 text-xs whitespace-nowrap">All Teams</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 mb-10 sm:grid-cols-3">
        <div class="stat">
            <p class="stat-figure gold"><x-money :amount="$team->budget" :size="18" /></p>
            <p class="stat-caption">Total Budget</p>
        </div>
        <div class="stat">
            <p class="stat-figure"><x-money :amount="$team->spent" :size="18" /></p>
            <p class="stat-caption">Spent</p>
        </div>
        <div class="stat">
            <p class="stat-figure up"><x-money :amount="$team->remainingBudget()" :size="18" /></p>
            <p class="stat-caption">Remaining</p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <p class="eyebrow gold">Squad ({{ $players->count() }})</p>
        <form method="GET" class="flex items-center gap-2">
            <label class="eyebrow" for="sort">Sort</label>
            <select name="sort" id="sort" class="field px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="value_desc" {{ $sort === 'value_desc' ? 'selected' : '' }}>Value — High to Low</option>
                <option value="value_asc" {{ $sort === 'value_asc' ? 'selected' : '' }}>Value — Low to High</option>
                <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name — A to Z</option>
                <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name — Z to A</option>
                <option value="role" {{ $sort === 'role' ? 'selected' : '' }}>Category</option>
                <option value="tier" {{ $sort === 'tier' ? 'selected' : '' }}>Tier</option>
                <option value="age_asc" {{ $sort === 'age_asc' ? 'selected' : '' }}>Age — Youngest First</option>
                <option value="age_desc" {{ $sort === 'age_desc' ? 'selected' : '' }}>Age — Oldest First</option>
            </select>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @forelse ($players as $player)
            <div class="card-section lift-on-hover p-4">
                <div class="player-portrait mb-3" style="aspect-ratio: 3/4;">
                    @if($player->image)
                        <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                    @else
                        <div class="initials">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                    @endif
                </div>
                <p class="text-sm font-semibold truncate" style="color: var(--paper);">{{ $player->name }}</p>
                <p class="text-xs" style="color: var(--paper-faint);">{{ $player->typeLabel() }}</p>
                <p class="text-xs" style="color: var(--paper-faint);">{{ $player->tier }} @if($player->age) &nbsp;—&nbsp; Age {{ $player->age }} @endif</p>
                <p class="text-sm font-semibold mt-2" style="color: var(--gold);"><x-money :amount="$player->current_value" /></p>
            </div>
        @empty
            <div class="col-span-full card-section p-12 text-center" style="color: var(--paper-faint);">No players in this squad yet</div>
        @endforelse
    </div>
@endsection
