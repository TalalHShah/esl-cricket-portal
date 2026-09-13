@extends('layouts.admin')

@section('title', $competition->name)

@section('content')
    <div class="mb-8 flex items-start justify-between flex-wrap gap-4">
        <div>
            <p class="eyebrow gold mb-2">{{ ucfirst($competition->type) }} @if($competition->is_official) &middot; Official ESL Competition @endif</p>
            <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">{{ $competition->name }}</h1>
            <p class="text-sm mt-2" style="color: var(--paper-faint);">
                <span class="status-pill {{ $competition->status === 'completed' ? 'confirmed' : 'pending' }}">{{ strtoupper(str_replace('_', ' ', $competition->status)) }}</span>
                @if ($competition->isLeague())
                    &middot; {{ $competition->rounds == 2 ? 'Double' : 'Single' }} round-robin &middot; {{ $competition->total_overs }} overs &middot; {{ $competition->has_playoffs ? 'With playoffs' : 'No playoffs' }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.competitions.edit', $competition) }}" class="btn-ghost px-4 py-2.5 text-sm">Edit Settings</a>
            <a href="{{ route('competitions.show', $competition) }}" class="btn-ghost px-4 py-2.5 text-sm" target="_blank">View Public Page</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <p class="eyebrow gold mb-3">Participating Teams ({{ $competition->teams->count() }})</p>
            <div class="card-section p-4 mb-4">
                @forelse ($competition->teams as $team)
                    <div class="flex items-center justify-between py-2" style="border-bottom: var(--rule);">
                        <span style="color: var(--paper);">{{ $team->name }}</span>
                        @if (! $competition->matches->count())
                            <form method="POST" action="{{ route('admin.competitions.teams.remove', [$competition, $team]) }}" onsubmit="return confirm('Remove {{ $team->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs" style="color: var(--live);">Remove</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm py-2" style="color: var(--paper-faint);">No teams added yet.</p>
                @endforelse
            </div>

            @if (! $competition->matches->count())
                <form method="POST" action="{{ route('admin.competitions.teams.add', $competition) }}" class="flex gap-2">
                    @csrf
                    <select name="team_id" class="field flex-1 px-3 py-2 text-sm" required>
                        <option value="">Add a team...</option>
                        @foreach ($allTeams as $team)
                            @unless ($competition->teams->contains('id', $team->id))
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endunless
                        @endforeach
                    </select>
                    <button type="submit" class="btn-accent px-4 py-2 text-sm whitespace-nowrap">Add</button>
                </form>
            @else
                <p class="text-xs" style="color: var(--paper-faint);">Fixtures already exist — the roster is locked. Delete the competition and start over to change teams.</p>
            @endif

            @if ($competition->isLeague())
                <div class="card-section p-4 mt-6">
                    <p class="eyebrow mb-2" style="color: var(--paper-faint);">League Fixtures</p>
                    @if ($leagueMatches->isEmpty())
                        <form method="POST" action="{{ route('admin.competitions.generate-fixtures', $competition) }}" onsubmit="return confirm('Generate the full round-robin fixture list now?');">
                            @csrf
                            <button type="submit" class="btn-accent w-full py-2.5 text-sm" @disabled($competition->teams->count() < 2)>Generate Round-Robin Fixtures</button>
                        </form>
                        @if ($competition->teams->count() < 2)
                            <p class="text-xs mt-2" style="color: var(--paper-faint);">Add at least 2 teams first.</p>
                        @endif
                    @else
                        <p class="text-sm" style="color: var(--paper-dim);">{{ $leagueMatches->count() }} fixtures generated ({{ $leagueMatches->where('status', 'confirmed')->count() }} confirmed).</p>
                    @endif
                </div>

                @if ($competition->has_playoffs)
                    <div class="card-section p-4 mt-4">
                        <p class="eyebrow mb-2" style="color: var(--paper-faint);">Playoffs</p>
                        @if ($playoffMatches->isEmpty())
                            <form method="POST" action="{{ route('admin.competitions.generate-playoffs', $competition) }}" onsubmit="return confirm('Generate Playoff 1 and Playoff 2 from the current standings?');">
                                @csrf
                                <button type="submit" class="btn-accent w-full py-2.5 text-sm" @disabled(! $leagueStageComplete)>Generate Playoffs From Standings</button>
                            </form>
                            @unless ($leagueStageComplete)
                                <p class="text-xs mt-2" style="color: var(--paper-faint);">Every league fixture must be confirmed first.</p>
                            @endunless
                        @else
                            <p class="text-sm" style="color: var(--paper-dim);">Bracket in progress — Qualifier and Final are created automatically as results come in.</p>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <div class="lg:col-span-2">
            @if ($competition->isLeague())
                <p class="eyebrow gold mb-3">Points Table</p>
                <div class="card-section overflow-x-auto mb-8">
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
                                    <td style="color: var(--paper);">{{ $row['team']->name }}</td>
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

                @if ($playoffMatches->isNotEmpty())
                    <p class="eyebrow gold mb-3">Playoff Bracket</p>
                    <div class="card-section overflow-x-auto mb-8">
                        <table class="data-table">
                            <thead><tr><th>Stage</th><th>Fixture</th><th>Date</th><th>Status</th><th>Result</th></tr></thead>
                            <tbody>
                                @foreach ($playoffMatches->sortBy(fn($m) => array_search($m->stage, ['playoff1','playoff2','qualifier2','final'])) as $match)
                                    <tr>
                                        <td style="color: var(--paper-dim);">{{ ['playoff1' => 'Playoff 1', 'playoff2' => 'Playoff 2', 'qualifier2' => 'Qualifier', 'final' => 'Final'][$match->stage] }}</td>
                                        <td style="color: var(--paper);">{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }}</td>
                                        <td style="color: var(--paper-faint);">{{ $match->match_date->format('d M Y') }}</td>
                                        <td><span class="status-pill {{ $match->status === 'confirmed' ? 'confirmed' : 'pending' }}">{{ strtoupper(str_replace('_',' ',$match->status)) }}</span></td>
                                        <td style="color: var(--gold);">{{ $match->winnerTeam?->name ?? '—' }}</td>
                                        <td style="text-align:right;"><a href="{{ route('admin.matches.edit', $match) }}" class="btn-ghost px-3 py-1.5 text-xs">Manage</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

            <p class="eyebrow gold mb-3">{{ $competition->isCup() ? 'Fixtures' : 'League Fixtures' }}</p>
            <div class="card-section overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Fixture</th><th>Leg</th><th>Date</th><th>Status</th><th>Result</th><th style="text-align:right;">Actions</th></tr></thead>
                    <tbody>
                        @forelse (($competition->isLeague() ? $leagueMatches : $competition->matches) as $match)
                            <tr>
                                <td style="color: var(--paper);">{{ $match->homeTeam?->name }} <span style="color: var(--paper-faint);">vs</span> {{ $match->awayTeam?->name }}</td>
                                <td style="color: var(--paper-faint);">{{ $match->leg ?? '—' }}</td>
                                <td style="color: var(--paper-dim);">{{ $match->match_date->format('d M Y') }}</td>
                                <td><span class="status-pill {{ $match->status === 'confirmed' ? 'confirmed' : 'pending' }}">{{ strtoupper(str_replace('_',' ',$match->status)) }}</span></td>
                                <td style="color: var(--gold);">{{ $match->winnerTeam?->name ?? '—' }}</td>
                                <td style="text-align:right;"><a href="{{ route('admin.matches.edit', $match) }}" class="btn-ghost px-3 py-1.5 text-xs">{{ $match->status === 'confirmed' ? 'View' : 'Edit / Record Result' }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">
                                {{ $competition->isCup() ? 'No fixtures yet — schedule one from Matches, tagging it to this competition.' : 'No fixtures generated yet' }}
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
