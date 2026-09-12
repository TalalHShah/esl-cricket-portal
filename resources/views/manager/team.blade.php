@extends('layouts.manager')

@section('title', 'Team Profile')

@section('content')
    @if(!$team)
        <div class="card-section p-12 text-center" style="color: var(--paper-faint);">You are not assigned to manage a team.</div>
    @else
        <div class="masthead mb-10">
            <div class="flex items-start gap-8">
                @if($team->logo)
                    <div class="crest" style="width:88px;height:88px;">
                        <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="crest" style="width:88px;height:88px; font-size: 2rem;">
                        {{ strtoupper(substr($team->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <p class="eyebrow gold mb-2">{{ $team->short_name }}</p>
                    <h1 class="font-display text-4xl md:text-5xl font-semibold mb-3" style="color: var(--paper);">{{ $team->name }}</h1>
                    @if($team->description)
                        <p class="text-lg" style="color: var(--paper-dim); max-width: 46rem;">{{ $team->description }}</p>
                    @endif
                </div>
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
            <p class="eyebrow gold">Full Squad ({{ $players->count() }} / {{ \App\Models\Team::SQUAD_LIMIT }})</p>
            <form method="GET" class="flex items-center gap-2 flex-wrap">
                <div class="flex items-center gap-1" style="background-color: var(--surface-raised); border: 1px solid var(--line-strong); border-radius: 2px;">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" class="px-3 py-2 text-xs font-semibold uppercase" style="{{ $view === 'grid' ? 'background-color: var(--gold); color: var(--ink);' : 'color: var(--paper-dim);' }}">Grid</a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'compact']) }}" class="px-3 py-2 text-xs font-semibold uppercase" style="{{ $view === 'compact' ? 'background-color: var(--gold); color: var(--ink);' : 'color: var(--paper-dim);' }}">Compact</a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}" class="px-3 py-2 text-xs font-semibold uppercase" style="{{ $view === 'table' ? 'background-color: var(--gold); color: var(--ink);' : 'color: var(--paper-dim);' }}">Table</a>
                </div>
                <input type="hidden" name="view" value="{{ $view }}">
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

        @if($view === 'table')
            <div class="card-section overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Category</th>
                            <th>Tier</th>
                            <th style="text-align:right;">Age</th>
                            <th style="text-align:right;">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($players as $player)
                            <tr class="row-hover" style="cursor: pointer;" onclick="window.location='{{ route('manager.players.show', $player) }}'">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="player-portrait" style="width: 40px; height: 52px; flex-shrink: 0;">
                                            @if($player->image)
                                                <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                                            @else
                                                <div class="initials" style="font-size: 0.7rem;">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                                            @endif
                                        </div>
                                        <span class="font-semibold" style="color: var(--paper);">{{ $player->name }}</span>
                                    </div>
                                </td>
                                <td style="color: var(--paper-dim);">{{ $player->typeLabel() }}</td>
                                <td><span class="tag {{ $player->is_manager_player ? 'gold' : '' }}">{{ $player->is_manager_player ? 'Manager' : $player->tier }}</span></td>
                                <td style="text-align:right; color: var(--paper-dim);">{{ $player->age ?? '—' }}</td>
                                <td style="text-align:right; font-weight: 600; color: var(--gold);"><x-money :amount="$player->current_value" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No players in squad yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif($view === 'compact')
            @include('partials.squad-compact', ['players' => $players])
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @forelse ($players as $player)
                    <a href="{{ route('manager.players.show', $player) }}" class="card-section lift-on-hover p-4 block">
                        <div class="player-portrait mb-3" style="aspect-ratio: 3/4;">
                            @if($player->image)
                                <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                            @else
                                <div class="initials">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                            @endif
                        </div>
                        <p class="text-sm font-semibold truncate" style="color: var(--paper);">{{ $player->name }}</p>
                        <p class="text-xs" style="color: var(--paper-faint);">{{ $player->typeLabel() }} &nbsp;—&nbsp; {{ $player->is_manager_player ? 'Manager' : $player->tier }}</p>
                        <p class="text-sm font-semibold mt-2" style="color: var(--gold);"><x-money :amount="$player->current_value" /></p>
                    </a>
                @empty
                    <div class="col-span-full card-section p-12 text-center" style="color: var(--paper-faint);">No players in squad yet</div>
                @endforelse
            </div>
        @endif
    @endif
@endsection
