@extends('layouts.admin')

@section('title', 'Schedule Match')

@section('content')
    @include('partials.page-header', ['title' => 'Schedule Match', 'eyebrow' => 'Fixtures'])

    <div class="card-section p-8 max-w-2xl">
        <form action="{{ route('admin.matches.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="eyebrow block mb-2">Home Team</label>
                    <select name="home_team_id" class="field w-full px-4 py-3" required>
                        <option value="">Select team...</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('home_team_id') == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('home_team_id') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="eyebrow block mb-2">Away Team</label>
                    <select name="away_team_id" class="field w-full px-4 py-3" required>
                        <option value="">Select team...</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('away_team_id') == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('away_team_id') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="eyebrow block mb-2">Competition</label>
                <select name="competition_id" class="field w-full px-4 py-3">
                    <option value="">None — standalone friendly</option>
                    @foreach ($competitions as $competition)
                        <option value="{{ $competition->id }}" @selected(old('competition_id') == $competition->id)>{{ $competition->name }} ({{ ucfirst($competition->type) }})</option>
                    @endforeach
                </select>
                <p class="text-xs mt-1" style="color: var(--paper-faint);">Tagging a competition here creates a one-off cup/league fixture — use the competition's own page to generate a full league schedule instead.</p>
                @error('competition_id') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="eyebrow block mb-2">Series (Optional)</label>
                <select name="series_id" class="field w-full px-4 py-3">
                    <option value="">None</option>
                    @foreach ($series as $s)
                        <option value="{{ $s->id }}" @selected(old('series_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs mt-1" style="color: var(--paper-faint);">Only active series (every team has accepted) are listed. Prefer adding fixtures from the series' own page.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="eyebrow block mb-2">Format (Optional)</label>
                    <select name="match_format" class="field w-full px-4 py-3">
                        <option value="">—</option>
                        <option value="T10" @selected(old('match_format') === 'T10')>T10</option>
                        <option value="T20" @selected(old('match_format') === 'T20')>T20</option>
                        <option value="ODI" @selected(old('match_format') === 'ODI')>ODI</option>
                        <option value="Test" @selected(old('match_format') === 'Test')>Test</option>
                    </select>
                </div>
                <div>
                    <label class="eyebrow block mb-2">Venue (Optional)</label>
                    <input type="text" name="venue" value="{{ old('venue') }}" placeholder="e.g., National Stadium" class="field w-full px-4 py-3">
                </div>
            </div>

            <div>
                <label class="eyebrow block mb-2">Match Date</label>
                <input type="datetime-local" name="match_date" class="field w-full px-4 py-3" value="{{ old('match_date', now()->format('Y-m-d\TH:i')) }}" required>
                @error('match_date') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-accent flex-1 py-3">Schedule Match</button>
                <a href="{{ route('admin.matches.index') }}" class="btn-ghost flex-1 py-3 text-center">Cancel</a>
            </div>
        </form>
    </div>
@endsection
