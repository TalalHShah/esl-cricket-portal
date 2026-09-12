@extends('layouts.app')

@section('title', $article->title)

@section('content')
    <div class="mb-6">
        <a href="{{ route('news.index') }}" class="eyebrow gold">&larr; Back to News</a>
    </div>

    <article class="max-w-3xl">
        <div class="mb-6">
            <span class="tag gold mb-3">{{ strtoupper($article->category ?? 'News') }}</span>
            @if($article->is_featured)
                <span class="tag mb-3 ml-2">Featured</span>
            @endif
            <h1 class="font-display text-3xl md:text-4xl font-semibold mt-4 mb-4" style="color: var(--paper);">{{ $article->title }}</h1>
            <p class="eyebrow">
                {{ $article->author?->name ?? 'Editorial' }} &nbsp;—&nbsp;
                {{ optional($article->published_at)->format('d M Y') }} &nbsp;—&nbsp;
                {{ number_format($article->views) }} views
            </p>
        </div>

        @if($article->cover_image)
            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full rounded-lg mb-8" style="max-height: 28rem; object-fit: cover;">
        @endif

        @if($article->excerpt)
            <p class="text-lg mb-6" style="color: var(--paper-dim);">{{ $article->excerpt }}</p>
        @endif

        <div class="text-base leading-relaxed" style="color: var(--paper-dim); white-space: pre-line;">{{ $article->body }}</div>
    </article>
@endsection
