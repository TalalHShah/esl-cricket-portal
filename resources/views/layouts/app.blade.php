<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ESL Cricket') &middot; Elite Series League</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/themes.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">

    {{-- Navigation Bar --}}
    <header class="sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                {{-- Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <div class="logo-badge w-12 h-12 rounded flex items-center justify-center">
                        <span class="text-white font-black text-xl">🏏</span>
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-xl font-black text-white tracking-tight">ESL CRICKET</h1>
                        <p class="text-xs font-bold" style="color: var(--accent);">ELITE SERIES LEAGUE</p>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-white hover:opacity-80 transition">Home</a>
                    <a href="{{ route('news.index') }}" class="text-sm font-semibold text-white hover:opacity-80 transition">News</a>
                    <a href="{{ route('teams.index') }}" class="text-sm font-semibold text-white hover:opacity-80 transition">Teams</a>
                    <a href="{{ route('admin.index') }}" class="text-sm font-semibold text-white hover:opacity-80 transition">Managers</a>
                    <a href="{{ route('transfers.index') }}" class="text-sm font-semibold text-white hover:opacity-80 transition">Transfers</a>
                </nav>

                {{-- Sign In Button --}}
                <div class="hidden sm:flex items-center gap-3">
                    <button onclick="alert('Manager Sign In coming soon')" class="btn-accent px-4 py-2 text-sm font-bold">
                        🔐 Sign In
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200 text-sm font-medium">
                ✓ {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-20 border-t border-emerald-500/10 bg-slate-950/50 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-sm text-slate-500">
            <p class="font-semibold text-slate-400 mb-2">🏏 ESL Cricket</p>
            <p>&copy; {{ date('Y') }} Elite Series League. Live Cricket, Live Drama, Live Champions.</p>
        </div>
    </footer>

</body>
</html>
