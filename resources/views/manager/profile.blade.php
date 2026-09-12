@extends('layouts.manager')

@section('title', 'Manager Profile')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white mb-2 flex items-center gap-2"><span>👤</span> Manager Profile</h1>
        <p class="text-lg text-slate-400">Manage your account details</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Profile Info --}}
        <div class="card-section rounded">
            <div class="card-header flex items-center gap-2">
                <span>ℹ️</span>
                <h2>Account Details</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover" style="border: 2px solid var(--accent);">
                    @else
                        <div class="team-badge-placeholder" style="width:64px;height:64px;background-color: var(--primary); border-color: var(--accent); border-radius: 9999px;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-black text-white text-lg">{{ $user->name }}</p>
                        <p class="text-sm text-slate-400">{{ ucfirst($user->role) }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('manager.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full rounded px-4 py-3 text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full rounded px-4 py-3 text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Profile Picture</label>
                        <input type="file" name="avatar" accept="image/*"
                               class="w-full rounded px-4 py-3 text-white border text-sm" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    </div>
                    <button type="submit" class="btn-accent px-6 py-3 text-sm font-bold rounded">Save Changes</button>
                </form>
            </div>
        </div>

        {{-- Password Change --}}
        <div class="card-section rounded">
            <div class="card-header flex items-center gap-2">
                <span>🔒</span>
                <h2>Change Password</h2>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('manager.profile.password') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full rounded px-4 py-3 text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">New Password</label>
                        <input type="password" name="password" required
                               class="w-full rounded px-4 py-3 text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full rounded px-4 py-3 text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    </div>
                    <button type="submit" class="btn-accent px-6 py-3 text-sm font-bold rounded">Update Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection
