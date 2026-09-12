@extends('layouts.app')

@section('title', 'Manager Sign In')

@section('content')
    <div class="max-w-md mx-auto mt-8">
        <div class="text-center mb-8">
            <div class="logo-badge w-16 h-16 rounded flex items-center justify-center mx-auto mb-4">
                <span class="text-white font-black text-2xl">🏏</span>
            </div>
            <h1 class="text-3xl font-black text-white">Manager Sign In</h1>
            <p class="text-slate-400 mt-2">Access your team's transfer market, auction room, and squad</p>
        </div>

        <div class="card-section rounded p-8">
            @if ($errors->any())
                <div class="mb-6 p-4 rounded border-l-4" style="background-color: var(--bg-tertiary); border-left-color: #DC2626;">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm font-medium text-white">⚠ {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded px-4 py-3 text-white border focus:outline-none"
                           style="background-color: var(--bg-tertiary); border-color: var(--border);"
                           placeholder="manager@esl.test">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Password</label>
                    <input type="password" name="password" required
                           class="w-full rounded px-4 py-3 text-white border focus:outline-none"
                           style="background-color: var(--bg-tertiary); border-color: var(--border);"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-400">
                        <input type="checkbox" name="remember" class="rounded">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="w-full btn-accent py-3 text-sm font-bold rounded">
                    Sign In to Manager Portal
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            Forgot your credentials? Contact the league administrator.
        </p>
    </div>
@endsection
