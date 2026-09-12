@extends('layouts.app')

@section('title', 'Edit Manager')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white">✏️ Edit Manager</h1>
        <p class="mt-2 text-slate-400">{{ $manager->name }}</p>
    </div>

    <div class="cricket-card rounded-2xl p-8 max-w-2xl">
        <form action="{{ route('admin.managers.update', $manager) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-white mb-2">Manager Name</label>
                <input type="text" name="name" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ old('name', $manager->name) }}" required>
                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">Email</label>
                <input type="email" name="email" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ old('email', $manager->email) }}" required>
                @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">Assigned Team</label>
                <select name="team_id" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-emerald-500 focus:outline-none transition" required>
                    <option value="">Select a team...</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" {{ old('team_id', $manager->managedTeam?->id) == $team->id ? 'selected' : '' }}>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
                @error('team_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="w-4 h-4" {{ old('is_active', $manager->is_active) ? 'checked' : '' }}>
                <label for="is_active" class="text-sm font-semibold text-white">Manager is active</label>
            </div>

            <div class="flex gap-3 pt-6">
                <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition">
                    Update Manager
                </button>
                <a href="{{ route('admin.managers.index') }}" class="flex-1 px-6 py-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
