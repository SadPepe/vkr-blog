<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PostPreview;
use Inertia\Inertia;

class AuthorController extends Controller
{
    public function show(User $user)
    {
        $articles = PostPreview::attachPaginator(
            $user->articles()
                ->with('user:id,name,avatar')
                ->select(array_merge(\App\Models\Article::CARD_COLUMNS, ['content']))
                ->latest()
                ->paginate(12)
        );

        $stats = $user->articles()
            ->selectRaw('
                COUNT(*) as articles_count,
                COALESCE(SUM(views_count), 0) as views_count,
                COALESCE(SUM(likes_count), 0) as likes_count,
                COALESCE(SUM(comments_count), 0) as comments_count
            ')
            ->first();

        return Inertia::render('Authors/Show', [
            'author' => [
                'id' => $user->id,
                'name' => $user->name,
                'about' => $user->about,
                'avatar_url' => $user->avatar_url,
                'cover_url' => $user->cover_url,
                'background_url' => $user->background_url,
                'created_at' => $user->created_at,
            ],
            'articles' => $articles,
            'media' => $user->articles()
                ->whereNotNull('card_image_path')
                ->latest()
                ->take(6)
                ->get(['id', 'slug', 'title', 'card_image_path']),
            'stats' => $stats,
        ]);
    }
}