<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNewsController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $query = NewsArticle::with('author');

        $sort = $this->applySort($query, $request, [
            'date_desc' => fn ($q) => $q->orderByDesc('created_at'),
            'date_asc' => fn ($q) => $q->orderBy('created_at'),
            'status' => fn ($q) => $q->orderBy('status'),
            'title_asc' => fn ($q) => $q->orderBy('title'),
            'views_desc' => fn ($q) => $q->orderByDesc('views'),
        ], 'date_desc');

        $articles = $query->paginate(15)->withQueryString();

        return view('admin.news.index', compact('articles', 'sort'));
    }

    public function create(): View
    {
        return view('admin.news.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request);

        $article = NewsArticle::create([
            ...$validated,
            'author_id' => Auth::id(),
            'published_at' => $validated['status'] === 'published' ? ($validated['published_at'] ?? now()) : ($validated['published_at'] ?? null),
        ]);

        return redirect()->route('admin.news.edit', $article)->with('status', 'Article created.');
    }

    public function edit(NewsArticle $newsArticle): View
    {
        return view('admin.news.edit', ['article' => $newsArticle]);
    }

    public function update(Request $request, NewsArticle $newsArticle): RedirectResponse
    {
        $validated = $this->validateArticle($request, $newsArticle);

        $newsArticle->update([
            ...$validated,
            'published_at' => $validated['status'] === 'published'
                ? ($validated['published_at'] ?? $newsArticle->published_at ?? now())
                : ($validated['published_at'] ?? null),
        ]);

        return redirect()->route('admin.news.edit', $newsArticle)->with('status', 'Article updated.');
    }

    public function destroy(NewsArticle $newsArticle): RedirectResponse
    {
        $newsArticle->delete();

        return redirect()->route('admin.news.index')->with('status', 'Article deleted.');
    }

    private function validateArticle(Request $request, ?NewsArticle $article = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'cover_image' => 'nullable|url|max:2048',
            'category' => 'required|in:match_report,transfer,announcement,preview,opinion',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        return $validated;
    }
}
