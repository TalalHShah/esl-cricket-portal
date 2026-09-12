@extends('layouts.admin')

@section('title', 'Match Result')

@section('content')
    @include('partials.page-header', [
        'title' => $match->homeTeam->name . ' vs ' . $match->awayTeam->name,
        'eyebrow' => 'Fixtures',
        'subtitle' => $match->match_date->format('d M Y, H:i'),
    ])

    @php $readOnly = $match->status === 'confirmed'; @endphp

    @if ($readOnly)
        <div class="mb-6 p-4 card-section" style="border-left: 2px solid var(--up);">
            <p class="text-sm font-medium" style="color: var(--paper);">This match is confirmed. Result and stats are locked and valuations have already been applied.</p>
        </div>
    @endif

    <form action="{{ route('admin.matches.update', $match) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="card-section p-8">
            <h2 class="font-display text-xl font-semibold mb-6" style="color: var(--paper);">Result</h2>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label class="eyebrow block mb-2">Match Date</label>
                    <input type="datetime-local" name="match_date" class="field w-full px-4 py-3" value="{{ old('match_date', $match->match_date->format('Y-m-d\TH:i')) }}" @disabled($readOnly) required>
                </div>
                <div>
                    <label class="eyebrow block mb-2">Winning Team</label>
                    <select name="winner_team_id" class="field w-full px-4 py-3" @disabled($readOnly)>
                        <option value="">Not decided yet</option>
                        <option value="{{ $match->home_team_id }}" @selected(old('winner_team_id', $match->winner_team_id) == $match->home_team_id)>{{ $match->homeTeam->name }}</option>
                        <option value="{{ $match->away_team_id }}" @selected(old('winner_team_id', $match->winner_team_id) == $match->away_team_id)>{{ $match->awayTeam->name }}</option>
                    </select>
                    @error('winner_team_id') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="eyebrow block mb-2">Status</label>
                    <select name="status" class="field w-full px-4 py-3" @disabled($readOnly)>
                        @foreach (['pending_review' => 'Pending Review', 'pending_confirmation' => 'Pending Confirmation', 'disputed' => 'Disputed'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $match->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <label class="eyebrow block mb-2">Summary Notes</label>
                <textarea name="summary_notes" rows="3" class="field w-full px-4 py-3" @disabled($readOnly)>{{ old('summary_notes', $match->summary_notes) }}</textarea>
            </div>
        </div>

        @foreach ([$match->homeTeam, $match->awayTeam] as $team)
            <div class="card-section overflow-x-auto">
                <div class="px-6 py-5" style="border-bottom: var(--rule);">
                    <h2 class="font-display text-lg font-semibold" style="color: var(--paper);">{{ $team->name }} — Player Stats</h2>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Runs</th>
                            <th>Balls</th>
                            <th>4s</th>
                            <th>6s</th>
                            <th>Overs</th>
                            <th>Maidens</th>
                            <th>Runs Conceded</th>
                            <th>Wickets</th>
                            <th>Catches</th>
                            <th>Stumpings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($team->players as $player)
                            @php $stat = $statsByPlayer->get($player->id); @endphp
                            <tr>
                                <td style="color: var(--paper);">{{ $player->name }}</td>
                                @foreach (['runs_scored', 'balls_faced', 'fours', 'sixes', 'overs_bowled', 'maidens', 'runs_conceded', 'wickets_taken', 'catches', 'stumpings'] as $field)
                                    <td>
                                        <input type="number" step="{{ $field === 'overs_bowled' ? '0.1' : '1' }}" min="0"
                                               name="stats[{{ $player->id }}][{{ $field }}]"
                                               class="field w-20 px-2 py-1.5 text-sm"
                                               value="{{ old("stats.$player->id.$field", $stat?->$field ?? '') }}"
                                               @disabled($readOnly)>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" style="text-align:center; padding: 2rem 0; color: var(--paper-faint);">No players signed to this team yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach

        @unless ($readOnly)
            <div class="flex gap-3">
                <button type="submit" class="btn-accent px-6 py-3">Save Result &amp; Stats</button>
                <a href="{{ route('admin.matches.index') }}" class="btn-ghost px-6 py-3">Back</a>
            </div>
        @endunless
    </form>

    @unless ($readOnly)
        <div class="mt-6 card-section p-6">
            <h3 class="font-display text-lg font-semibold mb-2" style="color: var(--paper);">Confirm Result</h3>
            <p class="text-sm mb-4" style="color: var(--paper-faint);">Once confirmed, the winning team is locked in and player valuations are automatically recalculated (win bonus, loss penalty, standout override). This cannot be undone — save the winner and stats above first.</p>
            <form method="POST" action="{{ route('admin.matches.confirm', $match) }}" onsubmit="return confirm('Confirm this result? Player valuations will be recalculated and the match will be locked.');">
                @csrf
                <button type="submit" class="btn-accent px-6 py-3" @disabled(! $match->winner_team_id)>Confirm Result &amp; Apply Valuations</button>
                @if (! $match->winner_team_id)
                    <span class="text-xs ml-3" style="color: var(--paper-faint);">Set and save a winning team first</span>
                @endif
            </form>
        </div>
    @endunless
@endsection
