@extends('layouts.app')

@section('title', 'Add Team')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white">➕ Add New Team</h1>
    </div>

    <div class="cricket-card rounded-2xl p-8 max-w-2xl">
        <form action="{{ route('admin.teams.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-white mb-2">Team Name</label>
                <input type="text" name="name" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" placeholder="e.g., Karachi Kings" value="{{ old('name') }}" required>
                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">Manager (Optional)</label>
                <select name="manager_id" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-emerald-500 focus:outline-none transition">
                    <option value="">Select a manager...</option>
                    @foreach (\App\Models\User::where('role', 'manager')->get() as $manager)
                        <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-white mb-2">Budget (PKR)</label>
                <input type="number" name="budget" class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition" placeholder="1000000" value="{{ old('budget', 1000000) }}" required>
                @error('budget') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-white mb-2">Primary Color</label>
                    <input type="color" name="primary_color" class="w-full h-12 rounded-lg cursor-pointer" value="{{ old('primary_color', '#10b981') }}" required>
                    @error('primary_color') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-white mb-2">Secondary Color</label>
                    <input type="color" name="secondary_color" class="w-full h-12 rounded-lg cursor-pointer" value="{{ old('secondary_color', '#047857') }}" required>
                    @error('secondary_color') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-6">
                <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold transition">
                    Create Team
                </button>
                <a href="{{ route('admin.teams.index') }}" class="flex-1 px-6 py-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
