<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(Request $request, Article $article)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = $article->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null
        ]);

        // Загружаем отношения для ответа
        $comment->load('user', 'replies.user');

        if ($request->wantsJson()) {
            return response()->json($comment);
        }

        return back()->with('success', 'Комментарий добавлен');
    }

    public function update(Request $request, Comment $comment)
    {
        Gate::authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment->update($validated);

        if ($request->wantsJson()) {
            return response()->json($comment);
        }

        return back()->with('success', 'Комментарий обновлен');
    }

    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $article = $comment->article;

        $comment->delete();

        // Обновляем счетчик комментариев статьи
        $article->decrement('comments_count');

        return back()->with('success', 'Комментарий удален');
    }
}
