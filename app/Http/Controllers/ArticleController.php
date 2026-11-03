<?php
// app/Http/Controllers/ArticleController.php

namespace App\Http\Controllers;

use App\Enums\ArticleCategory;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// Info Kampus: dibaca santri (dan peran lain yang login).
class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $category = ArticleCategory::tryFrom((string) $request->query('kategori'));

        $articles = Article::query()
            ->published()
            ->when($category, fn (Builder $q, ArticleCategory $c) => $q->where('category', $c))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn (Builder $q) => $q->where('title', 'like', $term)->orWhere('excerpt', 'like', $term));
            })
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('articles.index', [
            'articles' => $articles,
            'categories' => ArticleCategory::cases(),
            'currentCategory' => $category,
            'counts' => Article::published()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category'),
        ]);
    }

    public function show(Article $article): View
    {
        Gate::authorize('view', $article);

        return view('articles.show', [
            'article' => $article,
            'related' => Article::published()->where('category', $article->category)->whereKeyNot($article->id)->latest('published_at')->limit(3)->get(),
        ]);
    }
}
