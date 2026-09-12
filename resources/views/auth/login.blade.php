@extends('layouts.app')

@section('title', 'Manager Sign In')

@section('content')
    <div class="max-w-md mx-auto mt-8">
        <div class="text-center mb-10">
            <p class="eyebrow gold mb-3">Manager Portal</p>
            <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">Sign In</h1>
            <p class="mt-3 text-sm" style="color: var(--paper-faint);">Access your team's transfer market, auction room, and squad</p>
        </div>

        <div class="card-section p-8">
            @if ($errors->any())
                <div class="mb-6 p-4" style="border-left: 2px solid var(--live); background-color: var(--surface-raised);">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm font-medium" style="color: var(--paper);">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="eyebrow block mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="field w-full px-4 py-3" placeholder="manager@esl.test">
                </div>

                <div>
                    <label class="eyebrow block mb-2">Password</label>
                    <input type="password" name="password" required class="field w-full px-4 py-3" placeholder="••••••••">
                </div>

                <label class="flex items-center gap-2 text-sm" style="color: var(--paper-faint);">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>

                <button type="submit" class="btn-accent w-full py-3">Sign In</button>
            </form>
        </div>

        <p class="text-center text-xs mt-6" style="color: var(--paper-faint);">
            Forgot your credentials? Contact the league administrator.
        </p>
    </div>
@endsection
