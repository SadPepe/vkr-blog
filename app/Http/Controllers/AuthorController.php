<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class AuthorController extends Controller
{
    public function show(User $user)
    {
        $articles = $user->articles()
            ->with('user')
            ->latest()
            ->paginate(12);

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
                'created_at' => $user->created_at,
            ],
            'articles' => $articles,
            'stats' => $stats,
        ]);
    }
}