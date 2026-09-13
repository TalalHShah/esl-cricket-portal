<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/esl-logo.png') }}">

    <title>@yield('title', 'ESL Cricket') &middot; E-Sports League - Cricket</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Barlow+Condensed:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/themes.css') }}?v={{ filemtime(public_path('css/themes.css')) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body, input, select, button { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen">

    {{-- Navigation Bar --}}
    <header class="sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                {{-- Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 interactive">
                    <img src="{{ asset('images/esl-logo.png') }}" alt="ESL" class="h-14 w-auto" style="flex-shrink: 0;">
                    <div class="hidden sm:block leading-none">
                        <p class="eyebrow gold">E-Sports League - Cricket</p>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium underline-hover" style="color: var(--paper-dim);">Home</a>
                    <a href="{{ route('news.index') }}" class="text-sm font-medium underline-hover" style="color: var(--paper-dim);">News</a>
                    <a href="{{ route('teams.index') }}" class="text-sm font-medium underline-hover" style="color: var(--paper-dim);">Teams</a>
                    <a href="{{ route('managers.index') }}" class="text-sm font-medium underline-hover" style="color: var(--paper-dim);">Managers</a>
                    <a href="{{ route('transfers.index') }}" class="text-sm font-medium underline-hover" style="color: var(--paper-dim);">Transfers</a>
                </nav>

                {{-- Sign In Button --}}
                <div class="hidden sm:flex items-center gap-3">
                    @auth
                        <a href="{{ route('manager.dashboard') }}" class="btn-accent px-5 py-2.5">
                            Manager Portal
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost px-5 py-2.5">
                            Manager Sign In
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Mobile Nav --}}
            <nav class="md:hidden flex gap-2 overflow-x-auto pb-3 text-xs font-semibold">
                <a href="{{ route('dashboard') }}" class="whitespace-nowrap px-3 py-1.5" style="background-color: var(--surface-raised); color: var(--paper);">Home</a>
                <a href="{{ route('news.index') }}" class="whitespace-nowrap px-3 py-1.5" style="background-color: var(--surface-raised); color: var(--paper);">News</a>
                <a href="{{ route('teams.index') }}" class="whitespace-nowrap px-3 py-1.5" style="background-color: var(--surface-raised); color: var(--paper);">Teams</a>
                <a href="{{ route('managers.index') }}" class="whitespace-nowrap px-3 py-1.5" style="background-color: var(--surface-raised); color: var(--paper);">Managers</a>
                <a href="{{ route('transfers.index') }}" class="whitespace-nowrap px-3 py-1.5" style="background-color: var(--surface-raised); color: var(--paper);">Transfers</a>
                @auth
                    <a href="{{ route('manager.dashboard') }}" class="whitespace-nowrap px-3 py-1.5 font-bold" style="background-color: var(--gold); color: var(--ink);">Portal</a>
                @else
                    <a href="{{ route('login') }}" class="whitespace-nowrap px-3 py-1.5 font-bold" style="background-color: var(--gold); color: var(--ink);">Sign In</a>
                @endauth
            </nav>
        </div>
        <div class="brand-rule"></div>
    </header>

    {{-- Main Content --}}
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 p-4 card-section" style="border-left: 2px solid var(--up);">
                <p class="text-sm font-medium" style="color: var(--paper);">{{ session('status') }}</p>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-20 py-10" style="background-color: var(--surface); border-top: var(--rule);">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <p class="font-display text-lg font-semibold mb-2" style="color: var(--paper);">ESL CRICKET</p>
            <p class="text-sm" style="color: var(--paper-faint);">&copy; {{ date('Y') }} E-Sports League - Cricket</p>
        </div>
    </footer>

</body>
</html>
