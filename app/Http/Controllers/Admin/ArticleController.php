<?php
// app/Http/Controllers/Admin/ArticleController.php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleCategory;
use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Article::class);

        return view('admin.articles.index', [
            'articles' => Article::query()
                ->when(ArticleCategory::tryFrom((string) $request->query('kategori')), fn (Builder $q, ArticleCategory $c) => $q->where('category', $c))
                ->when($request->filled('q'), fn (Builder $q) => $q->where('title', 'like', '%'.$request->string('q')->trim().'%'))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'categories' => ArticleCategory::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Article::class);

        return view('admin.articles.form', ['article' => new Article, 'categories' => ArticleCategory::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Article::class);

        $article = Article::create([...$this->validated($request), 'author_id' => $request->user()->id]);

        return redirect()->route('admin.articles.index')->with('success', "Artikel \"{$article->title}\" disimpan.");
    }

    public function edit(Article $article): View
    {
        Gate::authorize('update', $article);

        return view('admin.articles.form', ['article' => $article, 'categories' => ArticleCategory::cases()]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        Gate::authorize('update', $article);

        $article->update($this->validated($request, $article));

        return redirect()->route('admin.articles.index')->with('success', "Artikel \"{$article->title}\" diperbarui.");
    }

    public function destroy(Article $article): RedirectResponse
    {
        Gate::authorize('delete', $article);

        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Artikel dihapus.');
    }

    private function validated(Request $request, ?Article $article = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(ArticleCategory::class)],
            'excerpt' => ['required', 'string', 'max:300'],
            'body' => ['required', 'string', 'max:30000'],
            'source_url' => ['nullable', 'url:http,https', 'max:255'],
            'publish' => ['boolean'],
        ]);

        $data['published_at'] = $request->boolean('publish') ? ($article?->published_at ?? now()) : null;
        unset($data['publish']);

        return $data;
    }
}
