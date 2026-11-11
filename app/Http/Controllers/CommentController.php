<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Comment;
use App\Support\ImageStore;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function store(Request $request, Article $article)
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $content = trim((string) ($validated['content'] ?? ''));
        $hasImage = $request->hasFile('image');

        if ($content === '' && ! $hasImage) {
            throw ValidationException::withMessages([
                'content' => 'Добавьте текст или фото — пустой комментарий отправить нельзя.',
            ]);
        }

        $comment = $article->comments()->create([
            'content' => $content,
            'image_path' => $hasImage ? ImageStore::storeUploaded($request->file('image'), 'comments') : null,
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null
        ]);

        // Счётчик считает только комментарии верхнего уровня (без ответов)
        if (!$comment->isReply()) {
            $article->increment('comments_count');
        }

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
            'content' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_image' => 'nullable|boolean',
        ]);

        $content = trim((string) ($validated['content'] ?? ''));
        $hasImage = $request->hasFile('image');
        $removeImage = ! $hasImage && $request->boolean('remove_image');
        $keepsImage = $hasImage || ($comment->image_path && ! $removeImage);

        if ($content === '' && ! $keepsImage) {
            throw ValidationException::withMessages([
                'content' => 'Добавьте текст или фото — пустой комментарий сохранить нельзя.',
            ]);
        }

        $imagePath = $comment->image_path;

        if ($hasImage) {
            self::deleteImage($comment->image_path);
            $imagePath = ImageStore::storeUploaded($request->file('image'), 'comments');
        } elseif ($removeImage) {
            self::deleteImage($comment->image_path);
            $imagePath = null;
        }

        $comment->update([
            'content' => $content,
            'image_path' => $imagePath,
        ]);

        if ($request->wantsJson()) {
            return response()->json($comment);
        }

        return back()->with('success', 'Комментарий обновлен');
    }

    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $article = $comment->article;

        // Ответы удаляются каскадом в БД, поэтому забираем их файлы заранее
        $imagePaths = Comment::where('parent_id', $comment->id)->pluck('image_path')
            ->push($comment->image_path);

        $comment->delete();

        $imagePaths->each(fn (?string $path) => self::deleteImage($path));

        // Обновляем счетчик комментариев статьи (только для верхнего уровня)
        if (!$comment->isReply()) {
            $article->decrement('comments_count');
        }

        return back()->with('success', 'Комментарий удален');
    }

    public function toggleLike(Request $request, Comment $comment)
    {
        $user = auth()->user();

        $like = $comment->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $comment->decrement('likes_count');
            $liked = false;
        } else {
            $comment->likes()->create(['user_id' => $user->id]);
            $comment->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $comment->fresh()->likes_count
        ]);
    }

    // Удаляет файл из публичного диска; внешние ссылки не трогаем
    private static function deleteImage(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
