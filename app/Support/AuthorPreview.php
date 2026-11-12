<?php

namespace App\Support;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Единая выборка авторов для каталога и рейла: только незаблокированные
 * пользователи хотя бы с одной опубликованной статьёй.
 */
final class AuthorPreview
{
    public static function query(): Builder
    {
        return User::query()
            ->whereNull('blocked_at')
            ->whereHas('articles', fn (Builder $query) => $query->where('status', Article::STATUS_PUBLISHED))
            ->withCount([
                'articles as articles_count' => fn (Builder $query) => $query->where('status', Article::STATUS_PUBLISHED),
            ])
            ->withMax([
                'articles as last_published_at' => fn (Builder $query) => $query->where('status', Article::STATUS_PUBLISHED),
            ], 'created_at')
            ->with(['latestPublishedArticle' => fn ($query) => $query->select('articles.*')]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function map(User $user): array
    {
        $latest = $user->latestPublishedArticle;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'about' => $user->about,
            'avatar_url' => $user->avatar_url,
            'articles_count' => (int) $user->articles_count,
            'last_published_at' => $user->last_published_at,
            'last_article' => $latest ? [
                'id' => $latest->id,
                'slug' => $latest->slug,
                'label' => Article::labelFor($latest->title, $latest->excerpt),
                'created_at' => optional($latest->created_at)->toIso8601String(),
            ] : null,
        ];
    }
}
