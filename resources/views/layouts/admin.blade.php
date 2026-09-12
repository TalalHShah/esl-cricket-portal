<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') &middot; ESL Cricket</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Barlow+Condensed:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/themes.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body, input, select, button { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen">

    <header class="sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <a href="{{ route('admin.panel') }}" class="flex items-center gap-3">
                    <div class="logo-badge w-10 h-10 flex items-center justify-center">
                        <span class="font-display font-semibold text-lg" style="color: var(--paper);">EC</span>
                    </div>
                    <div class="hidden sm:block leading-none">
                        <h1 class="font-display text-xl font-semibold tracking-wide" style="color: var(--paper);">ESL CRICKET</h1>
                        <p class="eyebrow gold mt-1">Admin Panel</p>
                    </div>
                </a>

                <div class="hidden sm:flex items-center gap-4">
                    <span class="text-sm" style="color: var(--paper-dim);">{{ auth()->user()->name }}</span>
                    @if(auth()->user()->managedTeam)
                        <a href="{{ route('manager.dashboard') }}" class="btn-accent px-5 py-2.5">
                            Manager Portal
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-ghost px-5 py-2.5">
                            Sign Out
                        </button>
                    </form>
                </div>

                <div class="sm:hidden flex items-center gap-2">
                    @if(auth()->user()->managedTeam)
                        <a href="{{ route('manager.dashboard') }}" class="btn-accent px-3 py-2 text-xs">Manager</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-ghost px-3 py-2 text-xs">Sign Out</button>
                    </form>
                </div>
            </div>
        </div>

        <div style="background-color: var(--surface); border-top: var(--rule); border-bottom: var(--rule);">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <nav class="manager-nav flex items-center overflow-x-auto">
                    @php
                        $navItems = [
                            ['route' => 'admin.panel', 'label' => 'Dashboard'],
                            ['route' => 'admin.managers.index', 'label' => 'Managers', 'active' => 'admin.managers.*'],
                            ['route' => 'admin.teams.index', 'label' => 'Teams', 'active' => 'admin.teams.*'],
                            ['route' => 'admin.players.index', 'label' => 'Players', 'active' => 'admin.players.*'],
                            ['route' => 'admin.auctions.index', 'label' => 'Auctions', 'active' => 'admin.auctions.*'],
                            ['route' => 'admin.settings.index', 'label' => 'Settings'],
                        ];
                    @endphp
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['active'] ?? $item['route']) ? 'active' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 p-4 card-section" style="border-left: 2px solid var(--up);">
                <p class="text-sm font-medium" style="color: var(--paper);">{{ session('status') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 card-section" style="border-left: 2px solid var(--live);">
                <p class="text-sm font-medium" style="color: var(--paper);">{{ session('error') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 p-4 card-section" style="border-left: 2px solid var(--live);">
                @foreach ($errors->all() as $error)
                    <p class="text-sm font-medium" style="color: var(--paper);">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-20 py-10" style="background-color: var(--surface); border-top: var(--rule);">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <p class="font-display text-lg font-semibold mb-2" style="color: var(--paper);">ESL CRICKET — ADMIN PANEL</p>
            <p class="text-sm" style="color: var(--paper-faint);">&copy; {{ date('Y') }} Emirates Sports League</p>
        </div>
    </footer>

</body>
</html>
