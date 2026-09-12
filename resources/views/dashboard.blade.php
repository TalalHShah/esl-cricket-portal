@extends('layouts.app')

@section('title', 'Home')

@section('content')
    {{-- Masthead --}}
    @if($latestNews->first())
        <div class="masthead mb-10">
            <p class="eyebrow live mb-3">Breaking</p>
            <a href="{{ route('news.show', $latestNews->first()) }}" class="font-display text-4xl md:text-5xl font-semibold mb-4 block hover:opacity-80 transition" style="color: var(--paper);">{{ $latestNews->first()->title }}</a>
            <p class="text-lg mb-4" style="color: var(--paper-dim); max-width: 46rem;">{{ $latestNews->first()->excerpt }}</p>
            <p class="eyebrow">{{ $latestNews->first()->created_at->format('d M Y — H:i') }}</p>
        </div>
    @endif

    {{-- Main Grid: News (Left) + Live (Right) --}}
    <div class="grid grid-cols-1 gap-10 lg:grid-cols-3 mb-14">
        {{-- Breaking News --}}
        <div class="lg:col-span-2">
            <p class="eyebrow gold mb-4">Latest</p>
            <div class="space-y-0">
                @forelse ($latestNews->skip(1)->take(5) as $article)
                    <a href="{{ route('news.show', $article) }}" class="news-row row-hover cursor-pointer block">
                        <div class="flex items-start justify-between gap-6">
                            <div class="flex-1">
                                <span class="tag mb-2">{{ strtoupper($article->category ?? 'News') }}</span>
                                <h3 class="text-lg font-semibold mt-2" style="color: var(--paper);">{{ $article->title }}</h3>
                                <p class="text-sm mt-1" style="color: var(--paper-faint);">{{ $article->excerpt }}</p>
                            </div>
                            <p class="eyebrow whitespace-nowrap">{{ $article->created_at->format('d M') }}</p>
                        </div>
                    </a>
                @empty
                    <div class="card-section p-10 text-center" style="color: var(--paper-faint);">No news available</div>
                @endforelse
            </div>
        </div>

        {{-- Live / Stream --}}
        <div>
            <p class="eyebrow gold mb-4">Watch</p>
            <div class="card-section">
                <div class="card-header">
                    <h2>Live Now</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="aspect-video flex items-center justify-center text-center" style="background-color: var(--surface-raised); border: 1px solid var(--line);">
                        <div>
                            <p class="eyebrow gold mb-1">Stream</p>
                            <p class="text-sm" style="color: var(--paper-faint);">No broadcast in progress</p>
                        </div>
                    </div>
                    <div class="aspect-video flex items-center justify-center text-center" style="background-color: var(--surface-raised); border: 1px solid var(--line);">
                        <div>
                            <p class="eyebrow gold mb-1">Highlights</p>
                            <p class="text-sm" style="color: var(--paper-faint);">Latest match recap</p>
                        </div>
                    </div>
                    @auth
                        @if(auth()->user()->managedTeam)
                            <a href="{{ route('manager.livestream') }}" class="btn-accent w-full py-3">View All Streams</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming Fixtures --}}
    <div class="mb-14">
        <p class="eyebrow gold mb-4">Schedule</p>
        <div class="card-section">
            @forelse ($recentMatches as $match)
                <div class="fixture-row">
                    <p class="eyebrow mb-2">{{ $match->match_date?->format('d M Y') }} &nbsp;—&nbsp; {{ $match->venue }}</p>
                    <p class="fixture-teams mb-3">
                        {{ $match->homeTeam?->name ?? 'TBD' }}<span class="vs">vs</span>{{ $match->awayTeam?->name ?? 'TBD' }}
                    </p>
                    <span class="status-pill {{ $match->status === 'confirmed' ? 'confirmed' : 'pending' }}">
                        {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                    </span>
                </div>
            @empty
                <div class="p-10 text-center" style="color: var(--paper-faint);">No fixtures scheduled</div>
            @endforelse
        </div>
    </div>

    {{-- Teams Overview --}}
    <div>
        <p class="eyebrow gold mb-4">The League</p>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($topTeams as $team)
                <a href="{{ route('teams.show', $team) }}" class="card-section lift-on-hover p-6 block">
                    <div class="flex items-center gap-4 mb-6">
                        @if($team->logo)
                            <div class="crest lift-on-hover" style="width:52px;height:52px;">
                                <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="crest" style="width:52px;height:52px; font-size: 1.1rem;">
                                {{ substr($team->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-display text-lg font-semibold" style="color: var(--paper);">{{ $team->name }}</h3>
                            <p class="text-xs" style="color: var(--paper-faint);">{{ $team->manager?->name ?? 'Unmanaged' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-4" style="border-top: var(--rule);">
                        <div>
                            <p class="stat-caption mb-0">Players</p>
                            <p class="text-sm font-semibold" style="color: var(--paper);">{{ $team->players_count ?? 0 }}</p>
                        </div>
                        <div class="text-right">
                            <p class="stat-caption mb-0">Budget</p>
                            <p class="text-sm font-semibold" style="color: var(--gold);"><x-money :amount="$team->budget" /></p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-sm" style="color: var(--paper-faint);">No teams available</div>
            @endforelse
        </div>
    </div>
@endsection
