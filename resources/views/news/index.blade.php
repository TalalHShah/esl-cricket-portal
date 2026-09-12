@extends('layouts.app')

@section('title', 'News')

@section('content')
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-4xl font-black text-white mb-2">📰 News & Updates</h1>
        <p class="text-lg text-slate-400">Latest stories from the Elite Series League</p>
    </div>

    {{-- Filter Section --}}
    <div class="card-section rounded mb-8 p-6">
        <form method="GET" action="{{ route('news.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Category</label>
                <select name="category" class="rounded px-3 py-2 text-sm text-white border" style="background-color: var(--bg-tertiary); border-color: var(--border);">
                    <option value="">All Categories</option>
                    @foreach (['match_report' => 'Match Report', 'transfer' => 'Transfer', 'announcement' => 'Announcement', 'preview' => 'Preview', 'opinion' => 'Opinion'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-accent px-4 py-2 text-sm font-bold rounded">Filter</button>
            <a href="{{ route('news.index') }}" class="px-4 py-2 text-sm font-semibold text-white rounded hover:opacity-80 transition" style="background-color: var(--bg-tertiary); border: 1px solid var(--border);">Reset</a>
        </form>
    </div>

    {{-- Featured Article (if available) --}}
    @if($articles->first())
        <div class="mb-8 featured-story rounded">
            <div class="flex items-start gap-6">
                <div class="flex-1">
                    <span class="news-badge">{{ $articles->first()->category ?? 'News' }}</span>
                    @if($articles->first()->is_featured)
                        <span class="news-badge urgent ml-2">FEATURED</span>
                    @endif
                    <h2 class="text-4xl font-black text-white mt-4 mb-3">{{ $articles->first()->title }}</h2>
                    <p class="text-lg text-blue-100 mb-4">{{ $articles->first()->excerpt }}</p>
                    <div class="flex items-center gap-4 text-sm text-blue-200">
                        <span>{{ $articles->first()->author?->name ?? 'Editorial' }}</span>
                        <span>{{ optional($articles->first()->published_at)->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- News Grid --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($articles->skip($articles->first() ? 1 : 0) as $article)
            <a href="#" class="card-section rounded overflow-hidden hover:shadow-lg transition h-full flex flex-col">
                <div class="p-6 flex flex-col h-full">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="news-badge">{{ $article->category ?? 'News' }}</span>
                        @if($article->is_featured)
                            <span class="text-xs font-bold uppercase px-2 py-1 rounded" style="background-color: var(--accent); color: var(--primary);">Featured</span>
                        @endif
                    </div>
                    <h3 class="text-xl font-black text-white mb-2 flex-1">{{ $article->title }}</h3>
                    @if($article->excerpt)
                        <p class="text-sm text-slate-400 mb-4 flex-1">{{ $article->excerpt }}</p>
                    @endif
                    <div class="flex items-center justify-between text-xs text-slate-500 border-t pt-4" style="border-color: var(--border);">
                        <span>{{ $article->author?->name ?? 'Editorial' }}</span>
                        <span>{{ optional($article->published_at)->format('d M Y') }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="md:col-span-2 lg:col-span-3">
                <div class="card-section rounded p-8 text-center text-slate-500">
                    <p class="text-lg">No published articles yet</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $articles->links() }}
    </div>
@endsection
