<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PostPreview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthorController extends Controller
{
    public function show(Request $request, User $user)
    {
        // Своя страница автора не нужна: у себя есть отдельный профиль
        if ($request->user()?->id === $user->id) {
            return redirect()->route('profile');
        }

        $articles = PostPreview::attachPaginator(
            $user->articles()
                ->visible()
                ->with(['user:id,name,avatar', 'latestComment.user:id,name,avatar'])
                ->select(array_merge(\App\Models\Article::CARD_COLUMNS, ['content']))
                ->latest()
                ->paginate(12)
        );

        $stats = $user->articles()
            ->visible()
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
                'background_blur' => (int) $user->background_blur,
                'links' => $user->links ?? [],
                'created_at' => $user->created_at,
            ],
            'articles' => $articles,
            // Лучшие статьи автора: рейтинг по просмотрам вместо прежней медиасетки
            'topArticles' => \App\Models\Article::topFor($user),
            'stats' => $stats,
        ]);
    }
}
