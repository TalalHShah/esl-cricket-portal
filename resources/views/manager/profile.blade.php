@extends('layouts.manager')

@section('title', 'Manager Profile')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Account</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Manager Profile</h1>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div class="card-section">
            <div class="card-header"><h2>Account Details</h2></div>
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    @if($user->avatar)
                        <div class="crest" style="width:64px;height:64px; border-radius: 9999px; overflow: hidden;">
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="crest" style="width:64px;height:64px; border-radius: 9999px;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-lg" style="color: var(--paper);">{{ $user->name }}</p>
                        <p class="eyebrow gold mt-1">{{ ucfirst($user->role) }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('manager.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="eyebrow block mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="field w-full px-4 py-3">
                    </div>
                    <div>
                        <label class="eyebrow block mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="field w-full px-4 py-3">
                    </div>
                    <div>
                        <label class="eyebrow block mb-2">Profile Picture</label>
                        <input type="file" name="avatar" accept="image/*" class="field w-full px-4 py-3 text-sm">
                    </div>
                    <button type="submit" class="btn-accent px-6 py-3">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card-section">
            <div class="card-header"><h2>Change Password</h2></div>
            <div class="p-6">
                <form method="POST" action="{{ route('manager.profile.password') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="eyebrow block mb-2">Current Password</label>
                        <input type="password" name="current_password" required class="field w-full px-4 py-3">
                    </div>
                    <div>
                        <label class="eyebrow block mb-2">New Password</label>
                        <input type="password" name="password" required class="field w-full px-4 py-3">
                    </div>
                    <div>
                        <label class="eyebrow block mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required class="field w-full px-4 py-3">
                    </div>
                    <button type="submit" class="btn-accent px-6 py-3">Update Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection
