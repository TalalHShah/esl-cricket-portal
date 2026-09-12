@extends('layouts.app')

@section('title', 'Home')

@section('content')
    {{-- Featured Story Section --}}
    @if($latestNews->first())
        <div class="mb-8 featured-story rounded">
            <div class="flex items-start gap-6">
                <div class="flex-1">
                    <span class="news-badge urgent">BREAKING</span>
                    <h1 class="text-4xl font-black text-white mt-4 mb-3">{{ $latestNews->first()->title }}</h1>
                    <p class="text-lg text-blue-100 mb-4">{{ $latestNews->first()->excerpt }}</p>
                    <p class="text-sm text-blue-200">{{ $latestNews->first()->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Grid: News (Left) + Live Streams (Right) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-8">
        {{-- Breaking News (2 cols) - Image Card Style --}}
        <div class="lg:col-span-2">
            <div class="space-y-4">
                @forelse ($latestNews->skip(1)->take(4) as $article)
                    <div class="rounded overflow-hidden cursor-pointer hover:shadow-lg transition relative h-32"
                         style="background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-dark) 100%);">
                        {{-- Background with low opacity text --}}
                        <div class="absolute inset-0 opacity-10 text-white overflow-hidden">
                            <p class="text-4xl font-black">{{ strtoupper(substr($article->title, 0, 3)) }}</p>
                        </div>

                        {{-- Content overlay --}}
                        <div class="relative h-full p-6 flex flex-col justify-between border-l-4" style="border-left-color: var(--accent);">
                            <div>
                                <span class="news-badge text-xs">{{ $article->category ?? 'News' }}</span>
                                <h3 class="text-lg font-black text-white mt-2 line-clamp-2">{{ $article->title }}</h3>
                            </div>
                            <p class="text-xs text-slate-400">{{ $article->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded p-8 text-center text-slate-500" style="background-color: var(--bg-secondary);">
                        No news available
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Live Streams (1 col) - Video Section --}}
        <div>
            <div class="card-section rounded overflow-hidden">
                <div class="card-header flex items-center gap-2">
                    <span class="text-2xl">🔴</span>
                    <h2>Live Now</h2>
                </div>
                <div class="p-6 space-y-4">
                    {{-- Placeholder for live stream --}}
                    <div class="aspect-video rounded-lg flex items-center justify-center text-slate-500 text-center" style="background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-dark) 100%);">
                        <div>
                            <p class="text-sm font-semibold mb-2">📺 Live Stream</p>
                            <p class="text-xs">YouTube/Stream embed here</p>
                        </div>
                    </div>
                    {{-- YouTube Video Placeholder --}}
                    <div class="aspect-video rounded-lg flex items-center justify-center text-slate-500 text-center" style="background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-dark) 100%);">
                        <div>
                            <p class="text-sm font-semibold mb-2">▶️ Latest Video</p>
                            <p class="text-xs">Match highlights</p>
                        </div>
                    </div>
                    <button class="w-full btn-accent px-4 py-2 text-sm font-bold rounded">
                        Watch All
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming Fixtures - Bold Section --}}
    <div class="card-section rounded mb-8">
        <div class="card-header flex items-center gap-2">
            <span class="text-2xl">🏟️</span>
            <h2>Upcoming Fixtures</h2>
        </div>
        <div class="divide-y" style="border-color: var(--border);">
            @forelse ($recentMatches as $match)
                <div class="p-6 hover:bg-blue-900/10 transition cursor-pointer border-l-4" style="border-left-color: var(--accent);">
                    <p class="text-sm text-slate-400 mb-2" style="font-weight: 600;">{{ $match->match_date?->format('d M Y') }} • {{ $match->venue }}</p>
                    <h3 class="text-3xl font-black text-white mb-3">
                        {{ $match->homeTeam?->name ?? 'TBD' }}
                        <span class="text-slate-500 font-normal text-lg">vs</span>
                        {{ $match->awayTeam?->name ?? 'TBD' }}
                    </h3>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-400">{{ $match->match_date?->format('H:i') }}</p>
                        <span class="px-3 py-1 text-xs font-bold text-white rounded" style="background-color: var(--primary);">
                            {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">No fixtures scheduled</div>
            @endforelse
        </div>
    </div>

    {{-- Teams Overview - Grid Section --}}
    <div>
        <h2 class="text-3xl font-black text-white mb-6 flex items-center gap-2">
            <span>🏏</span> Teams
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($topTeams as $team)
                <div class="card-section rounded p-6 hover:shadow-lg transition cursor-pointer">
                    <div class="flex items-center gap-4 mb-6">
                        @if($team->logo)
                            <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}"
                                 class="w-12 h-12 rounded object-cover" style="border: 2px solid var(--accent);">
                        @else
                            <div class="team-badge-placeholder" style="background-color: var(--primary); border-color: var(--accent); color: white;">
                                {{ substr($team->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-black text-white">{{ $team->name }}</h3>
                            <p class="text-xs text-slate-400">{{ $team->short_name }}</p>
                        </div>
                    </div>
                    <div class="space-y-3 border-t pt-4" style="border-color: var(--border);">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Manager</span>
                            <span class="text-sm font-semibold text-white">{{ $team->manager?->name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Players</span>
                            <span class="text-sm font-semibold text-white">{{ $team->players_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-400">Budget</span>
                            <span class="stat-value text-base">PKR {{ number_format($team->budget, 0) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-slate-500">No teams available</div>
            @endforelse
        </div>
    </div>

@endsection
