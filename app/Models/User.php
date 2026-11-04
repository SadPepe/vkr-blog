<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'about',
        'avatar',
        'cover_image',
        'background_image',
        'background_blur',
    ];

    /**
     * Вычисляемые поля, которые всегда попадают в JSON (нужны для аватара в лентах и комментариях).
     *
     * @var list<string>
     */
    protected $appends = ['avatar_url'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'background_blur' => 'integer',
        ];
    }

    public function getAvatarUrlAttribute()
    {
        return $this->profileImageUrl($this->avatar);
    }

    /**
     * Фото-обложка в шапке профиля.
     */
    public function getCoverUrlAttribute(): ?string
    {
        return $this->profileImageUrl($this->cover_image);
    }

    /**
     * Фон страницы профиля.
     */
    public function getBackgroundUrlAttribute(): ?string
    {
        return $this->profileImageUrl($this->background_image);
    }

    private function profileImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return str_starts_with($path, 'http')
            ? $path
            : asset('storage/' . $path);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function hasLiked(Article $article)
    {
        return $this->likes()->where('article_id', $article->id)->exists();
    }
}
