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

        @if ($match->competition_id)
            <div class="card-section p-8">
                <h2 class="font-display text-xl font-semibold mb-2" style="color: var(--paper);">Scoreline</h2>
                <p class="text-sm mb-6" style="color: var(--paper-faint);">Needed for the {{ $match->competition->name }} points table's Net Run Rate. Overs use cricket notation — 19.4 means 19 overs and 4 balls, not 19.4 decimal overs. Tick "All Out" if the side was bowled out before facing its full quota — NRR then credits them the competition's full {{ $match->competition->total_overs }} overs.</p>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="space-y-3">
                        <p class="eyebrow gold">{{ $match->homeTeam->name }}</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="eyebrow block mb-2">Runs</label>
                                <input type="number" min="0" name="home_runs" class="field w-full px-3 py-2" value="{{ old('home_runs', $match->home_runs) }}" @disabled($readOnly)>
                            </div>
                            <div>
                                <label class="eyebrow block mb-2">Overs</label>
                                <input type="number" min="0" max="{{ $match->competition->total_overs }}" step="0.1" name="home_overs" class="field w-full px-3 py-2" value="{{ old('home_overs', $match->home_overs) }}" @disabled($readOnly)>
                            </div>
                        </div>
                        <label class="inline-flex items-center gap-2 text-xs" style="color: var(--paper-dim);">
                            <input type="checkbox" name="home_all_out" value="1" @checked(old('home_all_out', $match->home_all_out)) @disabled($readOnly)>
                            All Out
                        </label>
                    </div>
                    <div class="space-y-3">
                        <p class="eyebrow gold">{{ $match->awayTeam->name }}</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="eyebrow block mb-2">Runs</label>
                                <input type="number" min="0" name="away_runs" class="field w-full px-3 py-2" value="{{ old('away_runs', $match->away_runs) }}" @disabled($readOnly)>
                            </div>
                            <div>
                                <label class="eyebrow block mb-2">Overs</label>
                                <input type="number" min="0" max="{{ $match->competition->total_overs }}" step="0.1" name="away_overs" class="field w-full px-3 py-2" value="{{ old('away_overs', $match->away_overs) }}" @disabled($readOnly)>
                            </div>
                        </div>
                        <label class="inline-flex items-center gap-2 text-xs" style="color: var(--paper-dim);">
                            <input type="checkbox" name="away_all_out" value="1" @checked(old('away_all_out', $match->away_all_out)) @disabled($readOnly)>
                            All Out
                        </label>
                    </div>
                </div>
            </div>
        @endif

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

    <div class="mt-6 card-section p-6">
        <h3 class="font-display text-lg font-semibold mb-4" style="color: var(--paper);">Screenshots &amp; Evidence</h3>

        @if ($match->screenshots->isNotEmpty())
            <div class="grid grid-cols-2 gap-4 mb-6 sm:grid-cols-4">
                @foreach ($match->screenshots as $screenshot)
                    <div class="card-section p-2">
                        <a href="{{ asset('storage/' . $screenshot->file_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $screenshot->file_path) }}" alt="{{ $screenshot->original_name }}" class="w-full rounded" style="aspect-ratio: 4/3; object-fit: cover;">
                        </a>
                        <p class="text-xs mt-2" style="color: var(--paper-faint);">{{ ucfirst($screenshot->type) }}</p>
                        @if ($screenshot->caption)
                            <p class="text-xs" style="color: var(--paper-dim);">{{ $screenshot->caption }}</p>
                        @endif
                        <form method="POST" action="{{ route('admin.matches.screenshots.destroy', [$match, $screenshot]) }}" onsubmit="return confirm('Remove this screenshot?');" class="mt-2">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs" style="color: var(--live);">Remove</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm mb-4" style="color: var(--paper-faint);">No screenshots uploaded yet.</p>
        @endif

        <form method="POST" action="{{ route('admin.matches.screenshots.store', $match) }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-3 sm:grid-cols-4 sm:items-end">
            @csrf
            <div class="sm:col-span-2">
                <label class="eyebrow block mb-2">Image</label>
                <input type="file" name="screenshot" accept="image/*" class="field w-full px-3 py-2 text-sm" required>
            </div>
            <div>
                <label class="eyebrow block mb-2">Type</label>
                <select name="type" class="field w-full px-3 py-2 text-sm">
                    <option value="scorecard">Scorecard</option>
                    <option value="result">Result</option>
                    <option value="dispute">Dispute</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn-accent w-full py-2.5">Upload</button>
            </div>
        </form>
        @error('screenshot') <p class="text-sm mt-2" style="color: var(--live);">{{ $message }}</p> @enderror
    </div>

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
