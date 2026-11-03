<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'article_id',
        'parent_id',
        'likes_count'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    // Проверяем, является ли комментарий ответом
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    // Лайкнул ли текущий пользователь комментарий
    public function getIsLikedAttribute()
    {
        if (!auth()->check()) {
            return false;
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', auth()->id());
        }

        return $this->likes()->where('user_id', auth()->id())->exists();
    }

    // Помечает комментарий как лайкнутый/не лайкнутый текущим пользователем
    public function markLikedByCurrentUser(): void
    {
        $this->is_liked = $this->getIsLikedAttribute();
    }
}
