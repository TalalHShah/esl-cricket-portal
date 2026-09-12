<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of published news articles.
     */
    public function index(Request $request): View
    {
        $articles = NewsArticle::with('author')
            ->where('status', 'published')
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('news.index', compact('articles'));
    }
}
