@extends('layouts.app')

@section('title', 'Edit Player')

@section('content')
    @include('partials.page-header', ['title' => 'Edit Player', 'eyebrow' => 'Admin', 'subtitle' => $player->name])

    <div class="card-section p-8 max-w-2xl">
        <form action="{{ route('admin.players.update', $player) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="eyebrow block mb-2">Photo</label>
                <div class="flex items-center gap-4 mb-3">
                    <div class="player-portrait" style="width: 64px; height: 82px; flex-shrink: 0;">
                        @if($player->image)
                            <img src="{{ asset('storage/' . $player->image) }}" alt="{{ $player->name }}">
                        @else
                            <div class="initials" style="font-size: 0.9rem;">{{ strtoupper(substr($player->name, 0, 2)) }}</div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input type="file" name="photo" accept="image/*" class="field w-full px-4 py-3 text-sm">
                        @if($player->image)
                            <label class="flex items-center gap-2 mt-2 text-xs" style="color: var(--paper-faint);">
                                <input type="checkbox" name="remove_photo" value="1">
                                Remove current photo
                            </label>
                        @endif
                    </div>
                </div>
                @error('photo') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="eyebrow block mb-2">Player Name</label>
                <input type="text" name="name" class="field w-full px-4 py-3" value="{{ old('name', $player->name) }}" required>
                @error('name') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="eyebrow block mb-2">Country</label>
                    <input type="text" name="country" class="field w-full px-4 py-3" value="{{ old('country', $player->country) }}" required>
                    @error('country') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="eyebrow block mb-2">Age</label>
                    <input type="number" name="age" class="field w-full px-4 py-3" value="{{ old('age', $player->age) }}" min="14" max="50">
                    @error('age') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="eyebrow block mb-2">Team</label>
                <select name="team_id" class="field w-full px-4 py-3">
                    <option value="">Free Agent</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" {{ old('team_id', $player->team_id) == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="eyebrow block mb-2">Role</label>
                    <select name="role" class="field w-full px-4 py-3" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('role', $player->role) == $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="eyebrow block mb-2">Tier</label>
                    <select name="tier" class="field w-full px-4 py-3" required>
                        @foreach ($tiers as $tier)
                            <option value="{{ $tier }}" {{ old('tier', $player->tier) == $tier ? 'selected' : '' }}>{{ $tier }}</option>
                        @endforeach
                    </select>
                    @error('tier') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="eyebrow block mb-2">Base Value</label>
                <input type="number" name="base_value" class="field w-full px-4 py-3" value="{{ old('base_value', $player->base_value) }}" required>
                @error('base_value') <p class="text-sm mt-1" style="color: var(--live);">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $player->is_active) ? 'checked' : '' }}>
                <span class="text-sm" style="color: var(--paper);">Player is active</span>
            </label>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-accent flex-1 py-3">Update Player</button>
                <a href="{{ route('admin.players.index') }}" class="btn-ghost flex-1 py-3 text-center">Cancel</a>
            </div>
        </form>
    </div>
@endsection
