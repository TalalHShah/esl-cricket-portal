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

        /* ---------- Persistent "what to do next" status banner ---------- */
        #liveStatusBanner {
            position: sticky; top: 0; z-index: 40;
            background: linear-gradient(90deg, rgba(232,178,61,0.14), rgba(232,178,61,0.04) 60%, transparent);
            border-bottom: 1px solid var(--gold-dim);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        #liveStatusBanner.is-urgent {
            background: linear-gradient(90deg, rgba(220,38,38,0.18), rgba(220,38,38,0.05) 60%, transparent);
            border-bottom-color: var(--live);
        }
        #liveStatusBanner .banner-inner { max-width: 64rem; margin: 0 auto; padding: 0.6rem 1rem; display: flex; align-items: center; gap: 0.65rem; }
        @media (min-width: 640px) { #liveStatusBanner .banner-inner { padding-left: 1.5rem; padding-right: 1.5rem; } }
        @media (min-width: 1024px) { #liveStatusBanner .banner-inner { padding-left: 2rem; padding-right: 2rem; } }
        .live-status-dot { width: 8px; height: 8px; border-radius: 9999px; background: var(--live); flex-shrink: 0; animation: liveStatusPulse 1.6s ease-out infinite; }
        @keyframes liveStatusPulse {
            0% { box-shadow: 0 0 0 0 rgba(220,38,38,0.55); }
            70% { box-shadow: 0 0 0 9px rgba(220,38,38,0); }
            100% { box-shadow: 0 0 0 0 rgba(220,38,38,0); }
        }
        #liveStatusText { font-size: 0.8rem; color: var(--paper-dim); animation: liveStatusIn 350ms ease-out; }
        #liveStatusText strong { color: var(--paper); }
        @keyframes liveStatusIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
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
                <div class="flex items-center gap-2">
                    <a href="{{ route('manager.draft') }}" class="btn-ghost px-4 py-2 text-xs" @if(request()->routeIs('manager.draft')) style="border-color: var(--gold); color: var(--gold);" @endif>Draft Room</a>
                    <a href="{{ route('manager.auction') }}" class="btn-ghost px-4 py-2 text-xs" @if(request()->routeIs('manager.auction*')) style="border-color: var(--gold); color: var(--gold);" @endif>Auction Room</a>
                    <a href="{{ route('manager.dashboard') }}" class="btn-ghost px-4 py-2 text-xs">Exit</a>
                </div>
            </div>
        </div>
        <div class="brand-rule"></div>
    </header>

    <div id="liveStatusBanner" class="hidden">
        <div class="banner-inner">
            <span class="live-status-dot"></span>
            <p id="liveStatusText">&nbsp;</p>
        </div>
    </div>

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

    <script>
        // Shared by the draft room and the auction room: a persistent,
        // always-visible "what to do next" banner pinned under the header
        // so it never scrolls out of view, however deep the page gets.
        window.setLiveStatus = function (html, urgent) {
            const banner = document.getElementById('liveStatusBanner');
            const text = document.getElementById('liveStatusText');
            if (!banner || !text) return;
            if (!html) { banner.classList.add('hidden'); return; }
            banner.classList.remove('hidden');
            banner.classList.toggle('is-urgent', !!urgent);
            if (text.innerHTML !== html) {
                text.innerHTML = html;
                text.style.animation = 'none';
                void text.offsetWidth;
                text.style.animation = '';
            }
        };
    </script>
</body>
</html>
