@extends('layouts.manager')

@section('title', $series->name)

@section('content')
    <a href="{{ route('manager.series.index') }}" class="text-sm" style="color: var(--paper-faint);">&larr; All Series</a>

    <div class="mb-8 mt-3 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow gold mb-2">{{ $series->isTriSeries() ? 'Tri-Series' : 'Bilateral Series' }}</p>
            <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">{{ $series->name }}</h1>
            <p class="text-sm mt-2" style="color: var(--paper-faint);">
                {{ $series->teams->pluck('name')->join(' vs ') }}
                @if ($series->venue) &nbsp;—&nbsp; {{ $series->venue }} @endif
            </p>
        </div>
        <span class="tag {{ $series->status === 'active' ? 'gold' : '' }}" style="font-size: 0.8rem; padding: 0.4rem 0.9rem;">{{ str_replace('_', ' ', $series->status) }}</span>
    </div>

    @if (session('status'))
        <div class="card-section p-4 mb-8" style="border-left: 2px solid var(--up); color: var(--up);">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="card-section p-4 mb-8" style="border-left: 2px solid var(--live); color: var(--live);">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="card-section overflow-hidden mb-6">
                <div class="card-header"><h2>Teams</h2></div>
                <div class="p-5 space-y-3">
                    @foreach ($series->teams as $t)
                        <div class="flex items-center justify-between">
                            <span style="color: var(--paper);">{{ $t->name }}</span>
                            <span class="tag {{ $t->pivot->status === 'accepted' ? 'gold' : '' }}">{{ $t->pivot->status }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($myInvite && $myInvite->status === 'invited')
                <div class="card-section p-5 mb-6" style="border-left: 2px solid var(--gold);">
                    <p class="text-sm font-semibold mb-3" style="color: var(--paper);">You've been invited to this series.</p>
                    <div class="flex gap-2">
                        <form action="{{ route('manager.series.respond', $series) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="response" value="accept">
                            <button type="submit" class="btn-accent w-full py-2.5 text-sm">Accept</button>
                        </form>
                        <form action="{{ route('manager.series.respond', $series) }}" method="POST" class="flex-1" onsubmit="return confirm('Decline this invite? The whole series will be cancelled.');">
                            @csrf
                            <input type="hidden" name="response" value="decline">
                            <button type="submit" class="btn-ghost w-full py-2.5 text-sm" style="color: var(--live);">Decline</button>
                        </form>
                    </div>
                </div>
            @endif

            @if (! in_array($series->status, ['completed', 'cancelled'], true))
                <form action="{{ route('manager.series.cancel', $series) }}" method="POST" onsubmit="return confirm('Cancel this series? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="btn-ghost w-full py-2.5 text-sm" style="color: var(--live); border-color: var(--live);">Cancel Series</button>
                </form>
            @endif
        </div>

        <div class="lg:col-span-2">
            @if ($canManage)
                <div class="card-section overflow-hidden mb-6">
                    <div class="card-header"><h2>Add Fixture</h2></div>
                    <form action="{{ route('manager.series.fixtures.store', $series) }}" method="POST" class="p-5 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="eyebrow block mb-2">Home Team</label>
                                <select name="home_team_id" class="field w-full px-3 py-2.5" required>
                                    <option value="">Select...</option>
                                    @foreach ($series->teams as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="eyebrow block mb-2">Away Team</label>
                                <select name="away_team_id" class="field w-full px-3 py-2.5" required>
                                    <option value="">Select...</option>
                                    @foreach ($series->teams as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="eyebrow block mb-2">Format</label>
                                <select name="match_format" class="field w-full px-3 py-2.5" required>
                                    <option value="T10">T10</option>
                                    <option value="T20" selected>T20</option>
                                    <option value="ODI">ODI</option>
                                    <option value="Test">Test</option>
                                </select>
                            </div>
                            <div>
                                <label class="eyebrow block mb-2">Date &amp; Time</label>
                                <input type="datetime-local" name="match_date" class="field w-full px-3 py-2.5" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="eyebrow block mb-2">Venue</label>
                                <input type="text" name="venue" value="{{ $series->venue }}" placeholder="Defaults to the series venue" class="field w-full px-3 py-2.5">
                            </div>
                        </div>
                        <button type="submit" class="btn-accent px-6 py-2.5">Add Fixture</button>
                    </form>
                </div>
            @endif

            <div class="card-section overflow-hidden">
                <div class="card-header"><h2>Fixtures</h2></div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Match</th>
                                <th>Format</th>
                                <th>Venue</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($series->matches as $match)
                                <tr>
                                    <td>
                                        @if (Route::has('admin.matches.edit') && auth()->user()->isAdmin())
                                            <a href="{{ route('admin.matches.edit', $match) }}" class="text-link font-semibold">{{ $match->homeTeam?->name }} vs {{ $match->awayTeam?->name }}</a>
                                        @else
                                            <span style="color: var(--paper);">{{ $match->homeTeam?->name }} vs {{ $match->awayTeam?->name }}</span>
                                        @endif
                                        @if ($match->winnerTeam)
                                            <p class="text-xs mt-1" style="color: var(--paper-faint);">Winner: {{ $match->winnerTeam->name }}</p>
                                        @endif
                                    </td>
                                    <td><span class="tag">{{ $match->match_format ?? '—' }}</span></td>
                                    <td style="color: var(--paper-dim);">{{ $match->venue ?? '—' }}</td>
                                    <td style="color: var(--paper-dim);">{{ $match->match_date?->format('d M Y, H:i') }}</td>
                                    <td><span class="status-pill {{ $match->status === 'confirmed' ? 'confirmed' : 'pending' }}">{{ str_replace('_', ' ', $match->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No fixtures yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
