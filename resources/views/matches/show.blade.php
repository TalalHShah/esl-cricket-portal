@extends('layouts.app')

@section('title', 'Match Details')

@section('content')
    @php
        $homeName = $match->homeTeam?->name ?? 'TBD';
        $awayName = $match->awayTeam?->name ?? 'TBD';
    @endphp

    <div class="masthead mb-10">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                @include('partials.status-badge', ['status' => $match->status])
                <span class="text-sm" style="color: var(--paper-faint);">{{ optional($match->match_date)->format('d M Y, H:i') }}</span>
            </div>
            <a href="{{ route('matches.index') }}" class="text-link text-sm">&larr; All matches</a>
        </div>

        <p class="fixture-teams text-center mb-2" style="font-size: 2.25rem;">
            {{ $homeName }}<span class="vs">vs</span>{{ $awayName }}
        </p>

        @if ($match->winnerTeam)
            <p class="text-center text-sm mt-4" style="color: var(--up);">
                Winner — <span class="font-semibold">{{ $match->winnerTeam->name }}</span>
            </p>
        @endif

        @if ($match->summary_notes)
            <div class="mt-6 p-4" style="background-color: var(--surface-raised); border: 1px solid var(--line);">
                <p class="text-sm" style="color: var(--paper-dim);">{{ $match->summary_notes }}</p>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 mt-8 pt-6 sm:grid-cols-4" style="border-top: var(--rule);">
            <div>
                <p class="stat-caption mb-1">Submitted By</p>
                <p class="text-sm" style="color: var(--paper);">{{ $match->submittedBy?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="stat-caption mb-1">Confirmed By</p>
                <p class="text-sm" style="color: var(--paper);">{{ $match->confirmedBy?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="stat-caption mb-1">Players</p>
                <p class="text-sm" style="color: var(--paper);">{{ $stats->count() }}</p>
            </div>
            <div>
                <p class="stat-caption mb-1">Screenshots</p>
                <p class="text-sm" style="color: var(--paper);">{{ $match->screenshots->count() }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        @foreach ([['team' => $match->homeTeam, 'rows' => $homeStats, 'label' => $homeName], ['team' => $match->awayTeam, 'rows' => $awayStats, 'label' => $awayName]] as $side)
            <div>
                <p class="eyebrow gold mb-4">{{ $side['label'] }}</p>
                <div class="card-section overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th style="text-align:right;">R</th>
                                <th style="text-align:right;">B</th>
                                <th style="text-align:right;">4s</th>
                                <th style="text-align:right;">6s</th>
                                <th style="text-align:right;">W</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($side['rows'] as $stat)
                                <tr>
                                    <td>
                                        @if ($stat->player)
                                            <a href="{{ route('players.show', $stat->player) }}" class="text-link font-medium">{{ $stat->player->name }}</a>
                                        @else
                                            <span style="color: var(--paper-faint);">Unknown</span>
                                        @endif
                                        @if ($stat->was_exceptional)
                                            <span class="ml-1 text-xs" style="color: var(--gold);" title="Exceptional performance">&#9733;</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right; color: var(--gold); font-weight: 600;">{{ $stat->runs_scored }}</td>
                                    <td style="text-align:right; color: var(--paper-dim);">{{ $stat->balls_faced }}</td>
                                    <td style="text-align:right; color: var(--paper-dim);">{{ $stat->fours }}</td>
                                    <td style="text-align:right; color: var(--paper-dim);">{{ $stat->sixes }}</td>
                                    <td style="text-align:right; color: var(--paper-dim);">{{ $stat->wickets_taken }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 2.5rem 0; color: var(--paper-faint);">No statistics recorded</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
