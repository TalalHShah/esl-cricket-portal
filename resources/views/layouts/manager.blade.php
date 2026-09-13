<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/esl-logo.png') }}">

    <title>@yield('title', 'Manager Dashboard') &middot; ESL Cricket</title>

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

    <header class="sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <a href="{{ route('manager.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/esl-logo.png') }}" alt="ESL" class="h-14 w-auto" style="flex-shrink: 0;">
                    <div class="hidden sm:block leading-none">
                        <p class="eyebrow gold">Manager Portal</p>
                    </div>
                </a>

                <div class="hidden sm:flex items-center gap-4">
                    @if(auth()->user()->managedTeam)
                        @php $sessionTeam = auth()->user()->managedTeam; @endphp
                        <a href="{{ route('manager.team') }}" class="flex items-center gap-2.5">
                            <div class="flex items-center" style="flex-shrink: 0;">
                                <div style="width: 34px; height: 34px; border-radius: 9999px; overflow: hidden; border: 2px solid var(--line-strong); background-color: var(--surface-raised); display: flex; align-items: center; justify-content: center;">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-bold" style="color: var(--paper-faint);">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div style="width: 22px; height: 22px; border-radius: 9999px; overflow: hidden; border: 2px solid var(--surface); background-color: var(--surface-raised); margin-left: -10px; display: flex; align-items: center; justify-content: center;">
                                    @if($sessionTeam->logo)
                                        <img src="{{ asset('storage/' . $sessionTeam->logo) }}" alt="{{ $sessionTeam->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span style="font-size: 0.6rem; font-weight: 700; color: var(--gold);">{{ strtoupper(substr($sessionTeam->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="leading-none">
                                <p class="text-sm font-semibold" style="color: var(--paper);">{{ auth()->user()->name }}</p>
                                <p class="text-xs mt-0.5" style="color: var(--paper-faint);">{{ $sessionTeam->name }}</p>
                            </div>
                        </a>
                        <div style="width: 1px; height: 28px; background-color: var(--line-strong);"></div>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.panel') }}" class="btn-accent px-5 py-2.5">
                            Admin Panel
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
                        <a href="{{ route('manager.team') }}" style="width: 32px; height: 32px; border-radius: 9999px; overflow: hidden; border: 2px solid var(--line-strong); background-color: var(--surface-raised); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-xs font-bold" style="color: var(--paper-faint);">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                            @endif
                        </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.panel') }}" class="btn-accent px-3 py-2 text-xs">Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-ghost px-3 py-2 text-xs">Sign Out</button>
                    </form>
                </div>
            </div>
        </div>

        <div style="background-color: var(--surface); border-top: var(--rule); border-bottom: var(--rule);">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 manager-nav-wrap">
                <nav class="manager-nav flex items-center overflow-x-auto">
                    @php
                        $navItems = [
                            ['route' => 'manager.dashboard', 'label' => 'Home'],
                            ['route' => 'manager.transfers', 'label' => 'Transfer Market', 'active' => ['manager.transfers*', 'manager.negotiations.*']],
                            ['route' => 'manager.draft', 'label' => 'Draft Room'],
                            ['route' => 'manager.auction', 'label' => 'Auction', 'active' => 'manager.auction*'],
                            ['route' => 'manager.scouts', 'label' => 'Scouts'],
                            ['route' => 'manager.teams.index', 'label' => 'Teams', 'active' => 'manager.teams.*'],
                            ['route' => 'manager.fixtures', 'label' => 'Fixtures'],
                            ['route' => 'manager.livestream', 'label' => 'Live Stream'],
                            ['route' => 'manager.team', 'label' => 'Team Profile'],
                            ['route' => 'manager.profile', 'label' => 'Manager Profile'],
                        ];
                    @endphp
                    @foreach ($navItems as $item)
                        @php
                            $patterns = (array) ($item['active'] ?? $item['route']);
                        @endphp
                        <a href="{{ route($item['route']) }}" class="{{ request()->routeIs(...$patterns) ? 'active' : '' }}">
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
            <p class="font-display text-lg font-semibold mb-2" style="color: var(--paper);">ESL CRICKET — MANAGER PORTAL</p>
            <p class="text-sm" style="color: var(--paper-faint);">&copy; {{ date('Y') }} E-Sports League - Cricket</p>
        </div>
    </footer>

    <script src="{{ asset('js/comma-input.js') }}"></script>
</body>
</html>
