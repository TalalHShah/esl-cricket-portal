@extends('layouts.admin')

@section('title', 'Add Player')

@section('content')
    @include('partials.page-header', ['title' => 'Add New Player', 'eyebrow' => 'Admin'])

    <div class="card-section p-8 max-w-2xl">
        <form action="{{ route('admin.players.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="eyebrow block mb-2">Photo</label>
                <input type="file" name="photo" accept="image/*" class="field w-full px-4 py-3 text-sm">
                @error('photo') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="eyebrow block mb-2">Player Name</label>
                <input type="text" name="name" class="field w-full px-4 py-3" placeholder="e.g., Virat Kohli" value="{{ old('name') }}" required>
                @error('name') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="eyebrow block mb-2">Country</label>
                    <input type="text" name="country" class="field w-full px-4 py-3" placeholder="e.g., Pakistan" value="{{ old('country') }}" required>
                    @error('country') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="eyebrow block mb-2">Age</label>
                    <input type="number" name="age" class="field w-full px-4 py-3" placeholder="e.g., 28" value="{{ old('age') }}" min="14" max="50">
                    @error('age') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="eyebrow block mb-2">Team (Optional)</label>
                <select name="team_id" class="field w-full px-4 py-3">
                    <option value="">Free Agent</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="eyebrow block mb-2">Category</label>
                <select name="role" id="role-select" class="field w-full px-4 py-3" required onchange="toggleStyleFields()">
                    <option value="">Select category...</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </select>
                @error('role') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div id="batting-style-field">
                <label class="eyebrow block mb-2">Batting Hand</label>
                <select name="batting_style" class="field w-full px-4 py-3">
                    <option value="">Select...</option>
                    @foreach ($battingStyles as $style)
                        <option value="{{ $style }}" {{ old('batting_style') == $style ? 'selected' : '' }}>{{ $style }}</option>
                    @endforeach
                </select>
                @error('batting_style') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div id="bowling-style-field">
                <label class="eyebrow block mb-2">Bowling Type</label>
                <select name="bowling_style" class="field w-full px-4 py-3">
                    <option value="">Select...</option>
                    @foreach ($bowlingStyles as $style)
                        <option value="{{ $style }}" {{ old('bowling_style') == $style ? 'selected' : '' }}>{{ $style }}</option>
                    @endforeach
                </select>
                @error('bowling_style') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="eyebrow block mb-2">Tier</label>
                <select name="tier" class="field w-full px-4 py-3" required>
                    <option value="">Select tier...</option>
                    @foreach ($tiers as $tier)
                        <option value="{{ $tier }}" {{ old('tier') == $tier ? 'selected' : '' }}>{{ $tier }}</option>
                    @endforeach
                </select>
                @error('tier') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="eyebrow block mb-2">Base Value</label>
                <input type="text" data-comma-input name="base_value" class="field w-full px-4 py-3" placeholder="500,000" value="{{ old('base_value', 500000) }}" required>
                @error('base_value') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" checked>
                <span class="text-sm" style="color: var(--paper);">Player is active</span>
            </label>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-accent flex-1 py-3">Create Player</button>
                <a href="{{ route('admin.players.index') }}" class="btn-ghost flex-1 py-3 text-center">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function toggleStyleFields() {
            const role = document.getElementById('role-select').value;
            const battingRequired = ['Batsman', 'Wicketkeeper', 'All-rounder'].includes(role);
            const bowlingRequired = ['Bowler', 'All-rounder'].includes(role);

            document.getElementById('batting-style-field').hidden = !battingRequired;
            document.getElementById('bowling-style-field').hidden = !bowlingRequired;
        }
        document.addEventListener('DOMContentLoaded', toggleStyleFields);
    </script>
@endsection
