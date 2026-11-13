<?php

namespace App\Support;

use App\Models\Comment;
use DOMDocument;
use DOMNode;
use App\Support\MentionRenderer;
use Illuminate\Support\Str;

/**
 * Короткое превью поста для ленты: первые блоки content в авторском порядке
 * (текст и картинки не переставляются), без тяжёлого хвоста статьи.
 */
class PostPreview
{
    public const TEXT_LIMIT = 1200;
    public const IMAGE_LIMIT = 3;

    /** Лимиты «развёрнутого» превью в ленте (кнопка «Развернуть»). */
    public const EXCERPT_TEXT_LIMIT = 6000;
    public const EXCERPT_IMAGE_LIMIT = 8;

    public static function make(string $html, int $textLimit = self::TEXT_LIMIT, int $imageLimit = self::IMAGE_LIMIT): string
    {
        return self::withMeta($html, $textLimit, $imageLimit)['html'];
    }

    /**
     * Развёрнутая часть поста: те же блоки в авторском порядке, но с большими лимитами.
     *
     * @return array{html: string, complete: bool}
     */
    public static function excerpt(
        string $html,
        int $textLimit = self::EXCERPT_TEXT_LIMIT,
        int $imageLimit = self::EXCERPT_IMAGE_LIMIT
    ): array {
        return self::withMeta($html, $textLimit, $imageLimit);
    }

    /**
     * @return array{html: string, complete: bool} complete — в превью попал весь текст поста
     */
    public static function withMeta(
        string $html,
        int $textLimit = self::TEXT_LIMIT,
        int $imageLimit = self::IMAGE_LIMIT
    ): array {
        if (trim($html) === '') {
            return ['html' => '', 'complete' => true];
        }

        // Строки через <br> — в отдельные абзацы, иначе в превью они слипаются
        $html = ContentNormalizer::paragraphs($html);

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><div id="post-preview-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('post-preview-root');

        if (! $root) {
            return ['html' => '', 'complete' => true];
        }

        $out = '';
        $textLength = 0;
        $images = 0;
        $complete = true;

        foreach (iterator_to_array($root->childNodes) as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }

            $plain = self::plainText($node);
            $plainLength = mb_strlen($plain);
            $hasImage = self::containsImage($node);
            $remaining = $textLimit - $textLength;

            // Блок с картинкой включаем, даже если текст уже набран,
            // но не больше лимита картинок
            if ($hasImage && $images >= $imageLimit && $plain === '') {
                $complete = false;
                break;
            }

            // Текст набран по лимиту: дальше только блоки-картинки без подписи
            if ($remaining <= 0 && $plain !== '') {
                $complete = false;
                break;
            }

            // Абзац не влезает целиком — режем по остатку лимита (минус многоточие),
            // чтобы превью набирало текст, а не обрывалось на первом большом абзаце
            if (! $hasImage && $plainLength > $remaining) {
                $out .= '<p>'.htmlspecialchars(mb_substr($plain, 0, max($remaining - 1, 0))).'…</p>';
                $complete = false;
                break;
            }

            if ($hasImage) {
                $images++;
            }

            $out .= $doc->saveHTML($node);
            $textLength += $plainLength;
        }

        return ['html' => self::trimEmptyEdges($out), 'complete' => $complete];
    }

    /**
     * Tiptap оставляет по краям пустые <p></p> (и <p><br></p>): в ленте они
     * добавляют пустую строку между телом поста и действиями под ним.
     */
    private static function trimEmptyEdges(string $html): string
    {
        $emptyParagraph = '<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>';

        $html = preg_replace('/^(?:\s*'.$emptyParagraph.')+/iu', '', $html) ?? $html;
        $html = preg_replace('/(?:\s*'.$emptyParagraph.')+\s*$/iu', '', $html) ?? $html;

        return trim($html);
    }

    /**
     * Готовит пагинатор ленты: подмешивает preview (и признак «есть ещё»)
     * и убирает полный content из ответа.
     */
    public static function attachPaginator($paginator)
    {
        // Ветки для превью: свежие верхние комментарии и ветки, где недавно отвечали.
        // Всё — парой запросов на страницу ленты, без N+1 по карточкам.
        $threads = self::previewThreads($paginator->getCollection()->pluck('id')->all());

        return $paginator->through(function ($article) use ($threads) {
            $preview = self::withMeta((string) $article->content);

            $article->preview = MentionRenderer::render($preview['html']);
            $article->has_more = ! $preview['complete'];

            // Кнопка лайка в ленте знает своё состояние сразу, без отдельного запроса
            $article->is_liked = auth()->check() ? (bool) $article->getIsLikedAttribute() : false;

            $article->recent_comments = collect($threads[$article->id] ?? [])
                ->map(fn (Comment $item) => self::mapComment($item, 220))
                ->values();

            return $article->makeHidden(['content']);
        });
    }

    /**
     * Комментарии для ленты: до трёх веток на статью.
     * В выборку обязательно попадает ветка с недавним ответом, даже если её
     * корень старше свежих комментариев; остальные места занимают свежие ветки.
     *
     * @param  array<int, int>  $articleIds
     * @return array<int, array<int, Comment>>
     */
    private static function previewThreads(array $articleIds, int $limit = 3): array
    {
        if ($articleIds === []) {
            return [];
        }

        $withThread = ['user:id,name,avatar', 'replies.user:id,name,avatar'];

        // Три последних верхних комментария на статью
        $latest = Comment::query()
            ->whereIn('article_id', $articleIds)
            ->whereNull('parent_id')
            ->whereNull('hidden_at')
            ->with($withThread)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('article_id')
            ->map(fn ($items) => $items->take(3)->values());

        // Свежие ответы (в том числе на старые комментарии): их корни тоже показываем
        $parentIds = Comment::query()
            ->whereIn('article_id', $articleIds)
            ->whereNotNull('parent_id')
            ->whereNull('hidden_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(60)
            ->pluck('parent_id')
            ->unique()
            ->values();

        $rootIds = collect();
        if ($parentIds->isNotEmpty()) {
            $rootIds = Comment::query()
                ->whereIn('id', $parentIds)
                ->get(['id', 'parent_id'])
                ->map(fn (Comment $parent) => $parent->parent_id ?? $parent->id)
                ->unique()
                ->values();
        }

        $replied = $rootIds->isEmpty()
            ? collect()
            : Comment::query()
                ->whereIn('id', $rootIds)
                ->whereNull('hidden_at')
                ->with($withThread)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('article_id');

        return collect($articleIds)->mapWithKeys(function (int $id) use ($latest, $replied, $limit) {
            $fresh = collect($latest[$id] ?? []);
            // Одна ветка с недавним ответом резервирует место, даже если её корень старый
            $alive = collect($replied[$id] ?? [])->first();

            $selected = $fresh
                ->reject(fn (Comment $thread) => $alive && $thread->id === $alive->id)
                ->sortByDesc('id')
                ->sortByDesc('created_at')
                ->take($alive ? max($limit - 1, 0) : $limit)
                ->values();

            if ($alive) {
                $selected->push($alive);
            }

            $merged = $selected
                // Свежие ветки — первыми: в ленте новый комментарий должен быть сразу виден
                ->sortByDesc('id')
                ->sortByDesc('created_at')
                ->values()
                ->all();

            return [$id => $merged];
        })->all();
    }

    /**
     * Комментарий вместе с видимыми ответами — для превью ветки в ленте.
     *
     * @return array<string, mixed>
     */
    private static function mapComment(Comment $comment, int $limit): array
    {
        return [
            ...self::mapCommentFields($comment, $limit),
            'replies' => $comment->relationLoaded('replies')
                ? $comment->replies
                    ->sortByDesc('id')
                    ->take(3)
                    ->sortBy('id')
                    ->map(fn (Comment $reply) => self::mapCommentFields($reply, 180))
                    ->values()
                    ->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapCommentFields(Comment $comment, int $limit): array
    {
        return [
            'id' => $comment->id,
            'body' => Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $comment->content))), $limit),
            'image_url' => $comment->image_url,
            'created_at' => optional($comment->created_at)->toIso8601String(),
            'user' => [
                'name' => $comment->user?->name,
                'avatar_url' => $comment->user?->avatar_url,
            ],
        ];
    }

    private static function plainText(DOMNode $node): string
    {
        return trim(preg_replace('/\s+/u', ' ', $node->textContent ?? '') ?? '');
    }

    private static function containsImage(DOMNode $node): bool
    {
        if ($node->nodeName === 'img') {
            return true;
        }

        foreach ($node->childNodes as $child) {
            if (self::containsImage($child)) {
                return true;
            }
        }

        return false;
    }
}
