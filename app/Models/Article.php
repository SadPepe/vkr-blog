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


}
