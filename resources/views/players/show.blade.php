@extends('layouts.app')

@section('title', $player->name)

@section('content')
    @php
        $team = $player->team;
        $primary = $team->primary_color ?? '#1D4ED8';
        $secondary = $team->secondary_color ?? '#0D1220';
    @endphp

    {{-- Hero --}}
    <div class="relative overflow-hidden mb-10 p-8 md:p-10"
         style="border-radius: 2px; border: 1px solid var(--line-strong); border-top: 3px solid var(--gold);
                background-image: {{ $team ? 'linear-gradient(165deg, rgba(4,6,14,0.35) 0%, rgba(4,6,14,0.9) 100%), linear-gradient(160deg, ' . $primary . ' 0%, ' . $secondary . ' 100%)' : 'none' }};
                background-color: {{ $team ? 'transparent' : 'var(--surface)' }};">

        @if($team && $team->logo)
            <img src="{{ asset('storage/' . $team->logo) }}" alt=""
                 style="position:absolute; top:-15%; right:-10%; width:60%; height:auto; opacity:0.18; pointer-events:none;">
        @endif

        <div class="relative flex items-start gap-8">
            <div class="player-portrait" style="width: 140px; height: 180px; flex-shrink: 0; {{ $team ? 'border-color: rgba(255,255,255,0.35); background-color: rgba(0,0,0,0.25);' : '' }}">
                @if($player->image)
                    <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                @else
                    <div class="initials" style="{{ $team ? 'color: rgba(255,255,255,0.7);' : '' }}">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                @endif
            </div>

            <div class="flex-1">
                <p class="eyebrow gold mb-2">{{ $team ? $team->name : 'Free Agent' }}</p>
                <h1 class="font-display text-4xl md:text-5xl font-semibold mb-2" style="color: {{ $team ? '#fff' : 'var(--paper)' }};">{{ $player->name }}</h1>
                <p class="text-base" style="color: {{ $team ? 'rgba(255,255,255,0.8)' : 'var(--paper-dim)' }};">{{ $player->typeLabel() }} — {{ $player->country }} — {{ $player->tier }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {{-- Profile --}}
        <div class="card-section p-6">
            <p class="eyebrow gold mb-4">Profile</p>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt style="color: var(--paper-faint);">Age</dt><dd style="color: var(--paper);">{{ $player->age ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt style="color: var(--paper-faint);">Batting Style</dt><dd style="color: var(--paper);">{{ $player->batting_style ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt style="color: var(--paper-faint);">Bowling Style</dt><dd style="color: var(--paper);">{{ $player->bowling_style ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt style="color: var(--paper-faint);">Status</dt><dd style="color: var(--paper);">{{ $player->isFreeAgent() ? 'Free Agent' : 'Contracted' }}</dd></div>
            </dl>

            <div class="mt-6 pt-4 space-y-2" style="border-top: var(--rule);">
                <div class="flex justify-between text-sm">
                    <span style="color: var(--paper-faint);">Base Value</span>
                    <span class="font-semibold" style="color: var(--paper);"><x-money :amount="$player->base_value" /></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span style="color: var(--paper-faint);">Current Value</span>
                    <span class="font-semibold" style="color: var(--gold);"><x-money :amount="$player->current_value" /></span>
                </div>
                @if (! is_null($player->sold_price))
                    <div class="flex justify-between text-sm">
                        <span style="color: var(--paper-faint);">Sold Price</span>
                        <span class="font-semibold" style="color: var(--up);"><x-money :amount="$player->sold_price" /></span>
                    </div>
                @endif
            </div>

            @if ($team)
                <a href="{{ route('teams.show', $team) }}" class="btn-ghost block w-full mt-6 py-2.5 text-center">{{ $team->name }}</a>
            @endif
        </div>

        {{-- Career totals --}}
        <div class="lg:col-span-2">
            <p class="eyebrow gold mb-4">Career Totals</p>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @include('partials.stat-card', ['label' => 'Matches', 'value' => $totals['matches']])
                @include('partials.stat-card', ['label' => 'Runs', 'value' => $totals['runs'], 'accent' => 'gold'])
                @include('partials.stat-card', ['label' => 'Balls Faced', 'value' => $totals['balls']])
                @include('partials.stat-card', ['label' => 'Fours', 'value' => $totals['fours']])
                @include('partials.stat-card', ['label' => 'Sixes', 'value' => $totals['sixes']])
                @include('partials.stat-card', ['label' => 'Wickets', 'value' => $totals['wickets'], 'accent' => 'up'])
            </div>
        </div>
    </div>

    @include('partials.player-transfer-history', ['transfers' => $transfers])

    {{-- Match stats --}}
    <div class="mt-10">
        <p class="eyebrow gold mb-4">Match Statistics</p>
        <div class="card-section overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th style="text-align:right;">Runs</th>
                        <th style="text-align:right;">Balls</th>
                        <th style="text-align:right;">4s</th>
                        <th style="text-align:right;">6s</th>
                        <th style="text-align:right;">Wkts</th>
                        <th style="text-align:right;">Overs</th>
                        <th style="text-align:right;">Catches</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stats as $stat)
                        <tr>
                            <td>
                                @if ($stat->match)
                                    <a href="{{ route('matches.show', $stat->match) }}" class="text-link font-medium">
                                        {{ $stat->match->homeTeam?->short_name ?? $stat->match->homeTeam?->name }}
                                        v
                                        {{ $stat->match->awayTeam?->short_name ?? $stat->match->awayTeam?->name }}
                                    </a>
                                    <span class="block text-xs mt-1" style="color: var(--paper-faint);">{{ optional($stat->match->match_date)->format('d M Y') }}</span>
                                @else
                                    <span style="color: var(--paper-faint);">—</span>
                                @endif
                            </td>
                            <td style="text-align:right; color: var(--gold); font-weight: 600;">{{ $stat->runs_scored }}</td>
                            <td style="text-align:right; color: var(--paper-dim);">{{ $stat->balls_faced }}</td>
                            <td style="text-align:right; color: var(--paper-dim);">{{ $stat->fours }}</td>
                            <td style="text-align:right; color: var(--paper-dim);">{{ $stat->sixes }}</td>
                            <td style="text-align:right; color: var(--paper-dim);">{{ $stat->wickets_taken }}</td>
                            <td style="text-align:right; color: var(--paper-dim);">{{ $stat->overs_bowled }}</td>
                            <td style="text-align:right; color: var(--paper-dim);">{{ $stat->catches }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No match statistics recorded yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
