@extends('layouts.manager')

@section('title', 'New Series')

@section('content')
    @include('partials.page-header', ['title' => 'New Series', 'eyebrow' => 'Bilateral & Tri-Series'])

    <div class="card-section p-8 max-w-2xl">
        @if ($errors->any())
            <div class="mb-6 p-4" style="border-left: 2px solid var(--live); color: var(--live);">
                @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
            </div>
        @endif

        <form action="{{ route('manager.series.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="eyebrow block mb-2">Series Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Friendship Cup 2026" class="field w-full px-4 py-3" required>
            </div>

            <div>
                <label class="eyebrow block mb-2">Series Type</label>
                <div class="flex gap-4 text-sm" style="color: var(--paper-dim);">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="series_type" value="bilateral" checked onchange="document.getElementById('secondOpponent').classList.add('hidden'); document.getElementById('secondOpponentSelect').removeAttribute('required'); document.getElementById('secondOpponentSelect').disabled = true; document.getElementById('secondOpponentSelect').value='';">
                        Bilateral (1 opponent)
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="series_type" value="tri" onchange="document.getElementById('secondOpponent').classList.remove('hidden'); document.getElementById('secondOpponentSelect').setAttribute('required', 'required'); document.getElementById('secondOpponentSelect').disabled = false;">
                        Tri-Series (2 opponents)
                    </label>
                </div>
            </div>

            <div>
                <label class="eyebrow block mb-2">Opponent</label>
                <select name="opponent_team_ids[]" class="field w-full px-4 py-3" required>
                    <option value="">Select a team...</option>
                    @foreach ($teams as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="secondOpponent" class="hidden">
                <label class="eyebrow block mb-2">Second Opponent (Tri-Series)</label>
                <select id="secondOpponentSelect" name="opponent_team_ids[]" class="field w-full px-4 py-3">
                    <option value="">Select a team...</option>
                    @foreach ($teams as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="eyebrow block mb-2">Default Venue</label>
                <input type="text" name="venue" value="{{ old('venue') }}" placeholder="e.g., National Stadium, Karachi" class="field w-full px-4 py-3">
                <p class="text-xs mt-2" style="color: var(--paper-faint);">Used as the default for fixtures in this series — each fixture can still set its own venue.</p>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-accent flex-1 py-3">Create &amp; Send Invites</button>
                <a href="{{ route('manager.series.index') }}" class="btn-ghost flex-1 py-3 text-center">Cancel</a>
            </div>
        </form>
    </div>
@endsection
