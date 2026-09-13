<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/esl-logo.png') }}">
    @yield('head')

    <title>@yield('title', 'Auction Room') &middot; ESL Cricket</title>

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

    <header style="background-color: var(--surface); border-bottom: var(--rule);">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/esl-logo.png') }}" alt="ESL" class="h-10 w-auto" style="flex-shrink: 0;">
                    <p class="eyebrow gold">Auction Room</p>
                </div>
                <a href="{{ route('manager.auction') }}" class="btn-ghost px-4 py-2 text-xs">Exit Room</a>
            </div>
        </div>
        <div class="brand-rule"></div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
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

</body>
</html>
