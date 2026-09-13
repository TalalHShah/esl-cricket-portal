@extends('layouts.admin')

@section('title', 'Edit Team')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white">Edit Team</h1>
        <p class="mt-2 text-slate-400">{{ $team->name }}</p>
    </div>

    <div class="cricket-card rounded-2xl p-8 max-w-2xl">
        <form action="{{ route('admin.teams.update', $team) }}" method="POST" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-white mb-2">Team Logo</label>
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if ($team->logo)
                            <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-slate-500 text-2xl font-black">{{ strtoupper(substr($team->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input type="file" name="logo" accept="image/*" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white text-sm focus:border-emerald-500 focus:outline-none transition">
                        @if ($team->logo)
                            <label class="flex items-center gap-2 mt-2 text-sm text-slate-400">
                                <input type="checkbox" name="remove_logo" value="1"> Remove current logo
                            </label>
                        @endif
                    </div>
                </div>
                @error('logo') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">Team Name</label>
                <input type="text" name="name" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ old('name', $team->name) }}" required>
                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">Manager</label>
                <select name="manager_id" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-emerald-500 focus:outline-none transition">
                    <option value="">Select a manager...</option>
                    @foreach ($managers as $manager)
                        <option value="{{ $manager->id }}" {{ old('manager_id', $team->manager_id) == $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">Budget (PKR)</label>
                <input type="text" data-comma-input name="budget" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" value="{{ old('budget', $team->budget) }}" required>
                @error('budget') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-white mb-2">Primary Color</label>
                    <input type="color" name="primary_color" class="w-full h-12 rounded-lg cursor-pointer" value="{{ old('primary_color', $team->primary_color) }}" required>
                    @error('primary_color') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-white mb-2">Secondary Color</label>
                    <input type="color" name="secondary_color" class="w-full h-12 rounded-lg cursor-pointer" value="{{ old('secondary_color', $team->secondary_color) }}" required>
                    @error('secondary_color') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-6">
                <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold transition">
                    Update Team
                </button>
                <a href="{{ route('admin.teams.index') }}" class="flex-1 px-6 py-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
