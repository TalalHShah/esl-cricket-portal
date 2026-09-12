@extends('layouts.app')

@section('title', 'Edit Player')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white">Edit Player</h1>
        <p class="mt-2 text-slate-400">{{ $player->name }}</p>
    </div>

    <div class="cricket-card rounded-2xl p-8 max-w-2xl">
        <form action="{{ route('admin.players.update', $player) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-white mb-2">Player Name</label>
                <input type="text" name="name" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ old('name', $player->name) }}" required>
                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">Team</label>
                <select name="team_id" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-emerald-500 focus:outline-none transition">
                    <option value="">Select a team...</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" {{ old('team_id', $player->team_id) == $team->id ? 'selected' : '' }}>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-white mb-2">Role</label>
                    <select name="role" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-emerald-500 focus:outline-none transition" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('role', $player->role) == $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-white mb-2">Tier</label>
                    <select name="tier" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-emerald-500 focus:outline-none transition" required>
                        @foreach ($tiers as $tier)
                            <option value="{{ $tier }}" {{ old('tier', $player->tier) == $tier ? 'selected' : '' }}>{{ $tier }}</option>
                        @endforeach
                    </select>
                    @error('tier') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">Base Value (PKR)</label>
                <input type="number" name="base_value" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ old('base_value', $player->base_value) }}" required>
                @error('base_value') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="w-4 h-4" {{ old('is_active', $player->is_active) ? 'checked' : '' }}>
                <label for="is_active" class="text-sm font-semibold text-white">Player is active</label>
            </div>

            <div class="flex gap-3 pt-6">
                <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold transition">
                    Update Player
                </button>
                <a href="{{ route('admin.players.index') }}" class="flex-1 px-6 py-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
