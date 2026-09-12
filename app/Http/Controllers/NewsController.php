<?php

namespace App\Http\Controllers;

use App\Concerns\Sortable;
use App\Models\NewsArticle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    use Sortable;

    /**
     * Display a single published news article.
     */
    public function show(NewsArticle $newsArticle): View
    {
        if ($newsArticle->status !== 'published' || ! $newsArticle->published_at || $newsArticle->published_at->isFuture()) {
            abort(404);
        }

        $newsArticle->increment('views');

        return view('news.show', ['article' => $newsArticle]);
    }

    /**
     * Display a listing of published news articles.
     */
    public function index(Request $request): View
    {
        $query = NewsArticle::with('author')
            ->where('status', 'published')
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')));

        $sort = $this->applySort($query, $request, [
            'featured' => fn ($q) => $q->orderByDesc('is_featured')->orderByDesc('published_at'),
            'date_desc' => fn ($q) => $q->orderByDesc('published_at'),
            'date_asc' => fn ($q) => $q->orderBy('published_at'),
            'title_asc' => fn ($q) => $q->orderBy('title'),
            'views_desc' => fn ($q) => $q->orderByDesc('views'),
        ], 'featured');

        $articles = $query->paginate(10)->withQueryString();

        return view('news.index', compact('articles', 'sort'));
    }
}
