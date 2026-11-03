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
    public const TEXT_LIMIT = 600;
    public const IMAGE_LIMIT = 2;

    public static function make(string $html, int $textLimit = self::TEXT_LIMIT, int $imageLimit = self::IMAGE_LIMIT): string
    {
        if (trim($html) === '') {
            return '';
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
            return '';
        }

        $out = '';
        $textLength = 0;
        $images = 0;

        foreach (iterator_to_array($root->childNodes) as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }

            $plain = self::plainText($node);
            $hasImage = self::containsImage($node);

            // Блок с картинкой включаем, даже если текст уже набран,
            // но не больше лимита картинок
            if ($hasImage && $images >= $imageLimit && $plain === '') {
                break;
            }

            if ($textLength > 0 && $textLength + mb_strlen($plain) > $textLimit) {
                break;
            }

            // Один огромный абзац (например, старые статьи) режем по лимиту
            if ($textLength === 0 && mb_strlen($plain) > $textLimit) {
                $out .= '<p>'.htmlspecialchars(mb_substr($plain, 0, $textLimit)).'…</p>';
                break;
            }

            if ($hasImage) {
                $images++;
            }

            $out .= $doc->saveHTML($node);
            $textLength += mb_strlen($plain);
        }

        return trim($out);
    }

    /**
     * Готовит пагинатор ленты: подмешивает preview и убирает полный content из ответа.
     */
    public static function attachPaginator($paginator)
    {
        return $paginator->through(function ($article) {
            $article->preview = self::make((string) $article->content);

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
