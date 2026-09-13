@extends('layouts.app')

@section('title', $competition->name)

@section('content')
    @include('partials.page-header', [
        'title' => $competition->name,
        'eyebrow' => ucfirst($competition->type) . ($competition->is_official ? ' · Official ESL Competition' : ''),
        'subtitle' => $competition->description,
    ])

    @if ($competition->isLeague())
        <div class="mb-10">
            <p class="eyebrow gold mb-3">Points Table</p>
            <div class="card-section overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Team</th><th>P</th><th>W</th><th>L</th><th>T</th><th>NRR</th><th>Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($standings as $i => $row)
                            <tr>
                                <td style="color: var(--paper-faint);">{{ $i + 1 }}</td>
                                <td style="color: var(--paper);">
                                    <a href="{{ route('teams.show', $row['team']) }}" style="color: inherit; text-decoration:none;">{{ $row['team']->name }}</a>
                                </td>
                                <td>{{ $row['played'] }}</td>
                                <td>{{ $row['won'] }}</td>
                                <td>{{ $row['lost'] }}</td>
                                <td>{{ $row['tied'] }}</td>
                                <td style="color: var(--paper-dim);">{{ $row['nrr'] >= 0 ? '+' : '' }}{{ number_format($row['nrr'], 3) }}</td>
                                <td style="color: var(--gold); font-weight: 600;">{{ $row['points'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align:center; padding: 2rem 0; color: var(--paper-faint);">No confirmed results yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($playoffMatches->isNotEmpty())
            <div class="mb-10">
                <p class="eyebrow gold mb-3">Playoff Bracket</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($playoffMatches->sortBy(fn($m) => array_search($m->stage, ['playoff1','playoff2','qualifier2','final'])) as $match)
                        <div class="card-section p-5">
                            <p class="eyebrow gold mb-2">{{ ['playoff1' => 'Playoff 1', 'playoff2' => 'Playoff 2', 'qualifier2' => 'Qualifier', 'final' => 'Final'][$match->stage] }}</p>
                            <p class="font-display text-lg font-semibold" style="color: var(--paper);">{{ $match->homeTeam->name }} <span style="color: var(--paper-faint); font-size:0.8em;">vs</span> {{ $match->awayTeam->name }}</p>
                            <p class="text-xs mt-1" style="color: var(--paper-faint);">{{ $match->match_date->format('d M Y') }}</p>
                            @if ($match->status === 'confirmed')
                                <p class="text-sm mt-2" style="color: var(--gold); font-weight:600;">Winner: {{ $match->winnerTeam->name }}</p>
                            @else
                                <span class="status-pill pending mt-2" style="display:inline-block;">Upcoming</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <div>
        <p class="eyebrow gold mb-3">{{ $competition->isCup() ? 'Fixtures' : 'League Fixtures' }}</p>
        <div class="card-section">
            @forelse (($competition->isLeague() ? $leagueMatches : $cupMatches)->sortBy('match_date') as $match)
                <a href="{{ route('matches.show', $match) }}" class="fixture-row row-hover block">
                    <p class="eyebrow mb-2">{{ optional($match->match_date)->format('d M Y, H:i') }}</p>
                    <p class="fixture-teams mb-1">{{ $match->homeTeam?->name }} <span style="color: var(--paper-faint);">vs</span> {{ $match->awayTeam?->name }}</p>
                    <span class="status-pill {{ $match->status === 'confirmed' ? 'confirmed' : 'pending' }}">{{ strtoupper(str_replace('_', ' ', $match->status)) }}</span>
                    @if ($match->winnerTeam)
                        <span class="text-sm ml-3" style="color: var(--gold);">Won by {{ $match->winnerTeam->name }}</span>
                    @endif
                </a>
            @empty
                <div class="p-10 text-center" style="color: var(--paper-faint);">No fixtures scheduled yet</div>
            @endforelse
        </div>
    </div>
@endsection
