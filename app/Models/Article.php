<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'card_image_path',
        'user_id',
        'category_slug',
        'views_count',
        'likes_count'
    ];
    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->orderBy('created_at');
    }

    public function allComments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Проверяем, лайкнул ли текущий пользователь статью
   

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

    // Метод для синхронизации счетчика
    public function syncLikesCount()
    {
        $this->update(['likes_count' => $this->likes()->count()]);
    }
    

    // Увеличиваем счетчик просмотров
    public function incrementViews()
    {
        $this->increment('views_count');
    }
}
