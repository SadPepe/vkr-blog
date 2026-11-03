<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Article extends Model
{
    /**
     * Колонки, достаточные для карточки статьи в лентах/сайдбарах.
     * Полный `content` (до 200 КБ) в список не отдаём.
     */
    public const CARD_COLUMNS = [
        'id',
        'title',
        'slug',
        'excerpt',
        'card_image_path',
        'category_slug',
        'created_at',
        'views_count',
        'likes_count',
        'comments_count',
        'user_id',
    ];

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'card_image_path',
        'user_id',
        'category_slug',
        'views_count',
        'likes_count',
        'comments_count'
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

    public function syncCommentsCount()
    {
        $this->update(['comments_count' => $this->comments()->count()]);
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

    public function views()
    {
        return $this->hasMany(ArticleView::class);
    }

    // Метод для увеличения просмотров
    public function incrementViews()
    {
        // Не накручиваем просмотры самому себе
        if (auth()->check() && auth()->id() === $this->user_id) {
            return;
        }

        $ip = request()->ip();

        // Учитываем один просмотр с одного IP в сутки
        $alreadyCounted = $this->views()
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->startOfDay())
            ->exists();

        if ($alreadyCounted) {
            return;
        }

        $this->increment('views_count');

        // Также записываем в таблицу просмотров
        ArticleView::create([
            'article_id' => $this->id,
            'ip_address' => $ip,
            'user_agent' => request()->userAgent()
        ]);
    }

    // Получаем количество просмотров за период
    public function getViewsCountForPeriod($period)
    {
        $date = match ($period) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            default => now()->subWeek()
        };

        return $this->views()->where('created_at', '>=', $date)->count();
    }

    // Популярные статьи за период (взвешенный рейтинг активности за неделю/месяц)
    public static function popularForPeriod(int $limit = 5, string $period = 'week', ?string $categorySlug = null)
    {
        $date = match ($period) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'all' => null,
            default => now()->subWeek(),
        };

        $sub = static::query()
            ->addSelect([
                'recent_views' => ArticleView::select(DB::raw('COUNT(*)'))
                    ->whereColumn('article_views.article_id', 'articles.id')
                    ->when($date, fn ($query) => $query->where('article_views.created_at', '>=', $date)),

                'recent_likes' => Like::select(DB::raw('COUNT(*)'))
                    ->whereColumn('likes.article_id', 'articles.id')
                    ->when($date, fn ($query) => $query->where('likes.created_at', '>=', $date)),

                'recent_comments' => Comment::select(DB::raw('COUNT(*)'))
                    ->whereColumn('comments.article_id', 'articles.id')
                    ->when($date, fn ($query) => $query->where('comments.created_at', '>=', $date))
                    ->whereNull('comments.parent_id'),
            ])
            ->getQuery();

        $columns = array_map(fn ($column) => 'popular.' . $column, static::CARD_COLUMNS);

        return static::hydrate(
            DB::table($sub, 'popular')
                ->select($columns)
                ->when($categorySlug, fn ($query) => $query->where('popular.category_slug', $categorySlug))
                ->orderByRaw('(recent_views * 1 + recent_likes * 2 + recent_comments * 1.5) DESC')
                ->limit($limit)
                ->get()
                ->all()
        )->load('user:id,name,avatar');
    }

}
