<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
     public function toggle(Article $article)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'error' => 'Необходимо авторизоваться'
            ], 401);
        }

        return DB::transaction(function () use ($user, $article) {
            $like = $article->likes()->where('user_id', $user->id)->first();

            if ($like) {
                // Убираем лайк
                $like->delete();
                $liked = false;
                $article->decrement('likes_count'); // Уменьшаем счетчик
            } else {
                // Ставим лайк
                $article->likes()->create(['user_id' => $user->id]);
                $liked = true;
                $article->increment('likes_count'); // Увеличиваем счетчик
            }

            // Обновляем модель
            $article->refresh();

            return response()->json([
                'liked' => $liked,
                'likes_count' => $article->likes_count
            ]);
        });
    }

    public function status(Article $article)
    {
        $user = auth()->user();
        $liked = $user ? $article->getIsLikedAttribute() : false;

        return response()->json([
            'liked' => $liked,
            'likes_count' => $article->likes_count // Используем поле из таблицы
        ]);
    }
}
