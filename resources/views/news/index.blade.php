@extends('layouts.app')

@section('title', 'News')

@section('content')
    <div class="mb-10">
        <p class="eyebrow gold mb-2">Coverage</p>
        <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">News &amp; Updates</h1>
    </div>

    <div class="card-section mb-10 p-6">
        <form method="GET" action="{{ route('news.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="eyebrow block mb-2">Category</label>
                <select name="category" class="field px-3 py-2 text-sm">
                    <option value="">All Categories</option>
                    @foreach (['match_report' => 'Match Report', 'transfer' => 'Transfer', 'announcement' => 'Announcement', 'preview' => 'Preview', 'opinion' => 'Opinion'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="eyebrow block mb-2">Sort</label>
                <select name="sort" class="field px-3 py-2 text-sm">
                    <option value="featured" @selected($sort === 'featured')>Featured First</option>
                    <option value="date_desc" @selected($sort === 'date_desc')>Newest First</option>
                    <option value="date_asc" @selected($sort === 'date_asc')>Oldest First</option>
                    <option value="title_asc" @selected($sort === 'title_asc')>Title — A to Z</option>
                    <option value="views_desc" @selected($sort === 'views_desc')>Most Viewed</option>
                </select>
            </div>
            <button type="submit" class="btn-accent px-5 py-2.5">Filter</button>
            <a href="{{ route('news.index') }}" class="btn-ghost px-5 py-2.5">Reset</a>
        </form>
    </div>

    @if($articles->first())
        <div class="masthead mb-10">
            <span class="tag gold mb-3">{{ strtoupper($articles->first()->category ?? 'News') }}</span>
            @if($articles->first()->is_featured)
                <span class="tag mb-3 ml-2">Featured</span>
            @endif
            <a href="{{ route('news.show', $articles->first()) }}" class="font-display text-3xl md:text-4xl font-semibold mt-4 mb-4 block hover:opacity-80 transition" style="color: var(--paper);">{{ $articles->first()->title }}</a>
            <p class="text-lg mb-4" style="color: var(--paper-dim); max-width: 46rem;">{{ $articles->first()->excerpt }}</p>
            <p class="eyebrow">{{ $articles->first()->author?->name ?? 'Editorial' }} &nbsp;—&nbsp; {{ optional($articles->first()->published_at)->format('d M Y') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($articles->skip($articles->first() ? 1 : 0) as $article)
            <a href="{{ route('news.show', $article) }}" class="card-section lift-on-hover p-6 flex flex-col h-full">
                <span class="tag mb-3" style="align-self: flex-start;">{{ strtoupper($article->category ?? 'News') }}</span>
                <h3 class="text-lg font-semibold mb-2 flex-1" style="color: var(--paper);">{{ $article->title }}</h3>
                @if($article->excerpt)
                    <p class="text-sm mb-4" style="color: var(--paper-faint);">{{ $article->excerpt }}</p>
                @endif
                <div class="flex items-center justify-between pt-4" style="border-top: var(--rule);">
                    <span class="text-xs" style="color: var(--paper-faint);">{{ $article->author?->name ?? 'Editorial' }}</span>
                    <span class="eyebrow">{{ optional($article->published_at)->format('d M Y') }}</span>
                </div>
            </a>
        @empty
            <div class="md:col-span-2 lg:col-span-3">
                <div class="card-section p-12 text-center" style="color: var(--paper-faint);">No published articles yet</div>
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $articles->links() }}
    </div>
@endsection
