<?php
// app/Policies/ArticlePolicy.php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    // Artikel yang sudah terbit boleh dibaca semua pengguna yang login.
    public function view(User $user, Article $article): bool
    {
        return $article->isPublished() || $user->checkPermissionTo('articles.manage');
    }

    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('articles.manage');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('articles.manage');
    }

    public function update(User $user, Article $article): bool
    {
        return $user->checkPermissionTo('articles.manage');
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->checkPermissionTo('articles.manage');
    }
}
