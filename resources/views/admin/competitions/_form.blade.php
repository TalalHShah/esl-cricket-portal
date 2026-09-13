@php $c = $competition ?? null; @endphp

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div>
        <label class="eyebrow block mb-2">Name</label>
        <input type="text" name="name" class="field w-full px-4 py-3" value="{{ old('name', $c?->name) }}" placeholder="e.g. ESL Premier League — Season 2" required>
        @error('name') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="eyebrow block mb-2">Type</label>
        <select name="type" class="field w-full px-4 py-3" required {{ $c ? 'disabled' : '' }}>
            <option value="league" @selected(old('type', $c?->type ?? 'league') === 'league')>League — round-robin + playoffs</option>
            <option value="cup" @selected(old('type', $c?->type) === 'cup')>Cup — standalone knockout/exhibition fixtures</option>
        </select>
        @if ($c)
            <input type="hidden" name="type" value="{{ $c->type }}">
            <p class="text-xs mt-1" style="color: var(--paper-faint);">Type can't be changed after creation.</p>
        @endif
    </div>
</div>

<div class="mt-6">
    <label class="eyebrow block mb-2">Description</label>
    <textarea name="description" rows="3" class="field w-full px-4 py-3" placeholder="Optional — shown on the public competition page">{{ old('description', $c?->description) }}</textarea>
</div>

<div class="grid grid-cols-1 gap-6 mt-6 sm:grid-cols-3" id="leagueFields" style="{{ old('type', $c?->type ?? 'league') === 'cup' ? 'display:none;' : '' }}">
    <div>
        <label class="eyebrow block mb-2">Round-Robin Legs</label>
        <select name="rounds" class="field w-full px-4 py-3">
            <option value="1" @selected(old('rounds', $c?->rounds ?? 2) == 1)>Single (each team plays once)</option>
            <option value="2" @selected(old('rounds', $c?->rounds ?? 2) == 2)>Double header (home &amp; away)</option>
        </select>
    </div>
    <div>
        <label class="eyebrow block mb-2">Playoffs</label>
        <select name="has_playoffs" class="field w-full px-4 py-3">
            <option value="1" @selected(old('has_playoffs', $c?->has_playoffs ?? true))>Yes — Playoff 1/2, Qualifier, Final</option>
            <option value="0" @selected(! old('has_playoffs', $c?->has_playoffs ?? true))>No — league table only</option>
        </select>
    </div>
    <div>
        <label class="eyebrow block mb-2">Fixture Spacing (days)</label>
        <input type="number" name="fixture_interval_days" min="1" max="30" class="field w-full px-4 py-3" value="{{ old('fixture_interval_days', $c?->fixture_interval_days ?? 3) }}">
    </div>
</div>

<div class="grid grid-cols-1 gap-6 mt-6 sm:grid-cols-2" id="startsOnField" style="{{ old('type', $c?->type ?? 'league') === 'cup' ? 'display:none;' : '' }}">
    <div>
        <label class="eyebrow block mb-2">First Fixture Date</label>
        <input type="date" name="starts_on" class="field w-full px-4 py-3" value="{{ old('starts_on', $c?->starts_on?->format('Y-m-d')) }}">
        <p class="text-xs mt-1" style="color: var(--paper-faint);">Used when generating fixtures — leave blank to start a week from today.</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 mt-6 sm:grid-cols-4">
    <div>
        <label class="eyebrow block mb-2">Overs Per Innings</label>
        <input type="number" name="total_overs" min="1" max="50" class="field w-full px-4 py-3" value="{{ old('total_overs', $c?->total_overs ?? 20) }}" required>
    </div>
    <div>
        <label class="eyebrow block mb-2">Points — Win</label>
        <input type="number" name="points_win" min="0" max="10" class="field w-full px-4 py-3" value="{{ old('points_win', $c?->points_win ?? 2) }}" required>
    </div>
    <div>
        <label class="eyebrow block mb-2">Points — Tie</label>
        <input type="number" name="points_tie" min="0" max="10" class="field w-full px-4 py-3" value="{{ old('points_tie', $c?->points_tie ?? 1) }}" required>
    </div>
    <div>
        <label class="eyebrow block mb-2">Points — Loss</label>
        <input type="number" name="points_loss" min="0" max="10" class="field w-full px-4 py-3" value="{{ old('points_loss', $c?->points_loss ?? 0) }}" required>
    </div>
</div>

<div class="mt-6">
    <label class="inline-flex items-center gap-2 text-sm" style="color: var(--paper-dim);">
        <input type="checkbox" name="is_official" value="1" @checked(old('is_official', $c?->is_official ?? true))>
        Official ESL competition (shown with an official badge publicly)
    </label>
</div>

<script>
    (function () {
        const typeSelect = document.querySelector('select[name="type"]:not([disabled])');
        if (!typeSelect) return;
        typeSelect.addEventListener('change', () => {
            const isCup = typeSelect.value === 'cup';
            document.getElementById('leagueFields').style.display = isCup ? 'none' : '';
            document.getElementById('startsOnField').style.display = isCup ? 'none' : '';
        });
    })();
</script>
