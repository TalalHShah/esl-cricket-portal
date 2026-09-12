@extends('layouts.admin')

@section('title', 'News')

@section('content')
    <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="eyebrow gold mb-2">Content</p>
            <h1 class="font-display text-4xl font-semibold" style="color: var(--paper);">News Articles</h1>
            <p class="text-sm mt-2" style="color: var(--paper-faint);">{{ $articles->total() }} article{{ $articles->total() !== 1 ? 's' : '' }}</p>
        </div>
        <div class="flex items-center gap-3">
            @include('partials.sort-bar', ['current' => $sort, 'sortId' => 'news', 'options' => [
                'date_desc' => 'Newest First',
                'date_asc' => 'Oldest First',
                'status' => 'Status',
                'title_asc' => 'Title A-Z',
                'views_desc' => 'Most Viewed',
            ]])
            <a href="{{ route('admin.news.create') }}" class="btn-accent px-5 py-3 whitespace-nowrap">+ New Article</a>
        </div>
    </div>

    <div class="card-section overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th style="text-align:right;">Views</th>
                    <th>Author</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($articles as $article)
                    <tr>
                        <td>
                            <p class="font-semibold" style="color: var(--paper);">{{ $article->title }}</p>
                            @if ($article->is_featured)
                                <span class="tag gold" style="font-size: 0.65rem;">Featured</span>
                            @endif
                        </td>
                        <td style="color: var(--paper-dim);">{{ ucfirst(str_replace('_', ' ', $article->category)) }}</td>
                        <td>
                            <span class="status-pill {{ $article->status === 'published' ? 'confirmed' : ($article->status === 'archived' ? 'pending' : 'pending') }}">
                                {{ strtoupper($article->status) }}
                            </span>
                        </td>
                        <td style="text-align:right; color: var(--paper-dim);">{{ number_format($article->views) }}</td>
                        <td style="color: var(--paper-faint);">{{ $article->author?->name ?? '—' }}</td>
                        <td style="text-align:right;">
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                <a href="{{ route('admin.news.edit', $article) }}" class="btn-ghost px-3 py-1.5 text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.news.destroy', $article) }}" onsubmit="return confirm('Delete this article?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost px-3 py-1.5 text-xs" style="color: var(--live);">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 3rem 0; color: var(--paper-faint);">No articles yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $articles->links() }}
    </div>
@endsection
