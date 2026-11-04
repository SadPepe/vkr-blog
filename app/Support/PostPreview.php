<?php

namespace App\Support;

use DOMDocument;
use DOMNode;

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

        return ['html' => trim($out), 'complete' => $complete];
    }

    /**
     * Готовит пагинатор ленты: подмешивает preview (и признак «есть ещё»)
     * и убирает полный content из ответа.
     */
    public static function attachPaginator($paginator)
    {
        return $paginator->through(function ($article) {
            $preview = self::withMeta((string) $article->content);

            $article->preview = $preview['html'];
            $article->has_more = ! $preview['complete'];

            return $article->makeHidden('content');
        });
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
