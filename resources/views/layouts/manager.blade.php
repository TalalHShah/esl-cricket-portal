<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Manager Dashboard') &middot; ESL Cricket</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/themes.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen">

    {{-- Manager Top Bar --}}
    <header class="sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <a href="{{ route('manager.dashboard') }}" class="flex items-center gap-2">
                    <div class="logo-badge w-12 h-12 rounded flex items-center justify-center">
                        <span class="text-white font-black text-xl">🏏</span>
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-xl font-black text-white tracking-tight">ESL CRICKET</h1>
                        <p class="text-xs font-bold" style="color: var(--accent);">MANAGER PORTAL</p>
                    </div>
                </a>

                <div class="hidden sm:flex items-center gap-3">
                    @if(auth()->user()->managedTeam)
                        <span class="text-sm text-slate-300">{{ auth()->user()->managedTeam->name }}</span>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-bold text-white rounded" style="background-color: var(--bg-tertiary); border: 1px solid var(--border);">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Manager Nav --}}
        <div style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border);">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center gap-1 overflow-x-auto py-2 text-sm font-bold">
                    @php
                        $navItems = [
                            ['route' => 'manager.dashboard', 'label' => 'Home', 'icon' => '🏠'],
                            ['route' => 'manager.transfers', 'label' => 'Transfer Market', 'icon' => '🔄'],
                            ['route' => 'manager.auction', 'label' => 'Auction', 'icon' => '🔨'],
                            ['route' => 'manager.scouts', 'label' => 'Scouts', 'icon' => '🔍'],
                            ['route' => 'manager.fixtures', 'label' => 'Fixtures', 'icon' => '🏟️'],
                            ['route' => 'manager.livestream', 'label' => 'Live Stream', 'icon' => '🔴'],
                            ['route' => 'manager.team', 'label' => 'Team Profile', 'icon' => '🛡️'],
                            ['route' => 'manager.profile', 'label' => 'Manager Profile', 'icon' => '👤'],
                        ];
                    @endphp
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="whitespace-nowrap px-4 py-2 rounded transition {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-400 hover:text-white' }}"
                           style="{{ request()->routeIs($item['route']) ? 'background-color: var(--primary);' : '' }}">
                            {{ $item['icon'] }} {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 p-4 rounded border-l-4" style="background-color: var(--bg-secondary); border-left-color: var(--success);">
                <p class="text-sm font-medium text-white">✓ {{ session('status') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 p-4 rounded border-l-4" style="background-color: var(--bg-secondary); border-left-color: #DC2626;">
                @foreach ($errors->all() as $error)
                    <p class="text-sm font-medium text-white">⚠ {{ $error }}</p>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-20 py-10" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border);">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-sm text-slate-500">
            <p class="font-semibold text-slate-400 mb-2">🏏 ESL Cricket — Manager Portal</p>
            <p>&copy; {{ date('Y') }} Elite Series League</p>
        </div>
    </footer>

</body>
</html>
