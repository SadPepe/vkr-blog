<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
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
        'username',
        'role',
        'blocked_at',
        'email',
        'password',
        'about',
        'avatar',
        'cover_image',
        'background_image',
        'background_blur',
        'links',
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
        'username',
        'role',
        'blocked_at',
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
            'links' => 'array',
            'blocked_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'privacy_consent_at' => 'datetime',
            'distribution_consent_at' => 'datetime',
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
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http')
            ? $path
            : asset('storage/'.$path);
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

    // --- Роли и блокировка -------------------------------------------------

    public const ROLE_USER = 'user';

    public const ROLE_MODERATOR = 'moderator';

    public const ROLE_ADMIN = 'admin';

    public const ROLES = [
        self::ROLE_USER => 'Пользователь',
        self::ROLE_MODERATOR => 'Модератор',
        self::ROLE_ADMIN => 'Администратор',
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    /**
     * Модератор или администратор — доступ в панель управления.
     */
    public function canModerate(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    public function isBlocked(): bool
    {
        return ! is_null($this->blocked_at);
    }

    /**
     * Доступ в панель управления: модератор или админ, не заблокированный.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canModerate() && ! $this->isBlocked();
    }
}
