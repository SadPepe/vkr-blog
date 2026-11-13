<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

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
        'status',
        'is_pinned',
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
        'status',
        'published_at',
        'is_pinned',
        'pinned_at',
        'views_count',
        'likes_count',
        'comments_count',
    ];

    protected $dates = [
        'published_at',
        'pinned_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'pinned_at' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_HIDDEN = 'hidden';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Черновик',
        self::STATUS_PUBLISHED => 'Опубликован',
        self::STATUS_HIDDEN => 'Скрыт',
    ];

    /**
     * Всё, что видят читатели. Черновики и скрытое — только автор и модераторы.
     */
    public function scopeVisible($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isVisible(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Закреплённый пост автора — первым в профиле и на странице автора.
     */
    public function scopePinnedFirst($query)
    {
        return $query->orderByDesc('is_pinned')->orderByDesc('pinned_at');
    }

    /**
     * Закреплять имеет смысл только опубликованное: скрытый пост читатель не увидит.
     */
    public function canBePinned(): bool
    {
        return $this->isVisible();
    }

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
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->whereNull('hidden_at')
            // Свежие обсуждения сверху: новый комментарий сразу виден под формой
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Один комментарий для превью в ленте: свежий в обсуждении, включая ответы.
     */
    public function latestComment()
    {
        return $this->hasOne(Comment::class)->whereNull('hidden_at')->latestOfMany();
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
        if (! auth()->check()) {
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
            'user_agent' => request()->userAgent(),
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

    /**
     * Метка для списков вне ленты: заголовок, иначе начало текста, иначе «Фотопост» —
     * в блоге заголовок необязателен, и пост может состоять из одних картинок.
     */
    public function label(int $limit = 60): string
    {
        return self::labelFor($this->title, $this->content, $limit);
    }

    /**
     * Та же метка для выборок, где не грузится content: заголовок, иначе начало
     * текста (excerpt), иначе «Фотопост». Пустой ответ не бывает — в афише
     * у поста всегда есть подпись.
     */
    public static function labelFor(?string $title, ?string $text, int $limit = 60): string
    {
        if (filled($title)) {
            return (string) $title;
        }

        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

        return $plain === '' ? 'Фотопост' : Str::limit($plain, $limit, '…');
    }

    /**
     * Пост без заголовка и без текста — «фотопост»: в афише у него вместо
     * подписи показывается миниатюра первой картинки.
     */
    public function isPhotoPost(): bool
    {
        return blank($this->title) && trim((string) $this->excerpt) === '';
    }

    /**
     * «Чаще читают» на странице автора: топ по просмотрам с короткой меткой.
     * Заголовок есть не у каждого поста, поэтому подпись собирается из текста.
     */
    public static function topFor(User $user, int $limit = 3)
    {
        return $user->articles()
            ->where('status', self::STATUS_PUBLISHED)
            ->orderByDesc('views_count')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get(['id', 'slug', 'title', 'excerpt', 'card_image_path', 'views_count'])
            ->map(fn (self $article) => [
                'id' => $article->id,
                'slug' => $article->slug,
                'views_count' => (int) $article->views_count,
                'card_image_path' => $article->card_image_path,
                'label' => self::labelFor($article->title, $article->excerpt),
                'is_photo_post' => $article->isPhotoPost(),
            ]);
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
            // Скрытые и черновики не должны всплывать в «Популярном»
            ->where('status', self::STATUS_PUBLISHED);

        if ($date === null) {
            // Детальные просмотры храним 12 месяцев, поэтому «за всё время»
            // считаем по денормализованному счётчику статьи.
            $sub->select('articles.*')->selectRaw('articles.views_count as recent_views');
        } else {
            $sub->addSelect([
                'recent_views' => ArticleView::select(DB::raw('COUNT(*)'))
                    ->whereColumn('article_views.article_id', 'articles.id')
                    ->where('article_views.created_at', '>=', $date),
            ]);
        }

        $sub->addSelect([
            'recent_likes' => Like::select(DB::raw('COUNT(*)'))
                ->whereColumn('likes.article_id', 'articles.id')
                ->when($date, fn ($query) => $query->where('likes.created_at', '>=', $date)),

            'recent_comments' => Comment::select(DB::raw('COUNT(*)'))
                ->whereColumn('comments.article_id', 'articles.id')
                ->when($date, fn ($query) => $query->where('comments.created_at', '>=', $date))
                ->whereNull('comments.parent_id'),
        ]);

        $sub = $sub->getQuery();

        $columns = array_map(fn ($column) => 'popular.'.$column, static::CARD_COLUMNS);

        return static::hydrate(
            DB::table($sub, 'popular')
                ->select($columns)
                ->when($categorySlug, fn ($query) => $query->where('popular.category_slug', $categorySlug))
                ->orderByRaw('(recent_views * 1 + recent_likes * 2 + recent_comments * 1.5) DESC')
                ->limit($limit)
                ->get()
                ->all()
        )->load('user:id,name,avatar')->each(function (self $article) {
            // Афиша: подпись есть всегда — заголовок, начало текста или «Фотопост»
            $article->label = self::labelFor($article->title, $article->excerpt);
            $article->is_photo_post = $article->isPhotoPost();

            return $article;
        });
    }
}
