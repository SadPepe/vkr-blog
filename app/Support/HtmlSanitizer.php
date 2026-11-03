<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Белый-list санитайзер HTML для контента статей.
 *
 * Работает через DOMDocument: вырезает любые теги и атрибуты вне
 * разрешённого списка, чистит URL (href/src) от опасных схем
 * (javascript:, data:text/html, vbscript: и т.п.) и убирает
 * обработчики событий (on*, style).
 */
class HtmlSanitizer
{
    /** Разрешённые теги. */
    protected static array $allowedTags = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'p', 'br', 'hr',
        'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'code', 'pre',
        'ul', 'ol', 'li',
        'blockquote',
        'a', 'img',
        'span', 'div',
        'figure', 'figcaption',
    ];

    /** Разрешённые атрибуты по тегам ('*' — глобально). */
    protected static array $tagAttributes = [
        '*'  => ['class', 'title', 'alt'],
        'a'  => ['href', 'rel', 'target'],
        'img'=> ['src'],
        'ol' => ['start'],
        // Выравнивание текста из редактора — единственное разрешённое свойство style.
        'p'  => ['style'],
        'h1' => ['style'],
        'h2' => ['style'],
        'h3' => ['style'],
        'h4' => ['style'],
        'h5' => ['style'],
        'h6' => ['style'],
    ];

    /** Единственное свойство style, которое разрешено сохранять. */
    protected static string $safeStylePattern = '/^\s*text-align\s*:\s*(left|center|right|justify)\s*;?\s*$/i';

    /** Схемы URL, которые разрешено оставлять. */
    protected static array $allowedSchemes = ['http', 'https', 'mailto', 'tel'];

    public static function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Страховка от явно встроенных скриптов/стилей до разбора DOM.
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);

        libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        // Подсказка кодировки, чтобы кириллица и спецсимволы читались корректно.
        $dom->loadHTML(
            '<?xml encoding="UTF-8"?><html><body>' . $html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $body = $dom->getElementsByTagName('body')->item(0);

        if ($body instanceof DOMElement) {
            // Обрабатываем ДЕТЕЙ body, не трогая сам контейнер.
            foreach (iterator_to_array($body->childNodes) as $child) {
                self::purgeBlock($child);
            }

            $result = '';
            foreach ($body->childNodes as $child) {
                $result .= $dom->saveHTML($child);
            }

            libxml_clear_errors();

            return trim($result);
        }

        libxml_clear_errors();

        return '';
    }

    /**
     * Рекурсивно очищает дерево: убирает комментарии и запрещённые
     * элементы (с содержимым для void-тегов) и чистит атрибуты.
     */
    protected static function purgeBlock(DOMNode $node): void
    {
        if ($node->hasChildNodes()) {
            $children = iterator_to_array($node->childNodes);
            foreach ($children as $child) {
                self::purgeBlock($child);
            }
        }

        if ($node instanceof \DOMComment) {
            $node->parentNode?->removeChild($node);

            return;
        }

        if (!$node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->tagName);
        if (!in_array($tag, self::$allowedTags, true)) {
            self::unwrap($node);

            return;
        }

        self::sanitizeAttributes($node);
    }

    /** Заменяет элемент его содержимым (убирая сам тег). */
    protected static function unwrap(DOMElement $node): void
    {
        $parent = $node->parentNode;
        if (!$parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }
        $parent->removeChild($node);
    }

    /** Оставляет только разрешённые атрибуты с безопасными значениями. */
    protected static function sanitizeAttributes(DOMElement $node): void
    {
        $tag = strtolower($node->tagName);

        $allowed = static::$tagAttributes['*'] ?? [];
        foreach (static::$tagAttributes as $t => $attrs) {
            if ($t === '*' || $t === $tag) {
                $allowed = array_merge($allowed, (array) $attrs);
            }
        }
        $allowed = array_unique($allowed);

        $toRemove = [];
        foreach (iterator_to_array($node->attributes) as $attr) {
            $name = strtolower($attr->name);
            $value = trim($attr->value);

            if (!in_array($name, $allowed, true)) {
                $toRemove[] = $attr->name;
                continue;
            }

            if ($name === 'href' || $name === 'src') {
                if (!self::isSafeUrl($value, $name)) {
                    $toRemove[] = $attr->name;
                }
                continue;
            }

            if ($name === 'target') {
                if ($value !== '_blank') {
                    $toRemove[] = $attr->name;
                }
                continue;
            }

            if ($name === 'rel') {
                $toRemove[] = $attr->name; // пересоздаётся ниже
                continue;
            }

            if ($name === 'class' && !preg_match('/^[a-zA-Z0-9_\-\s]+$/', $value)) {
                $toRemove[] = $attr->name;
                continue;
            }

            if ($name === 'style' && !preg_match(static::$safeStylePattern, $value)) {
                $toRemove[] = $attr->name;
                continue;
            }

            if ($name === 'start' || $name === 'colspan' || $name === 'rowspan') {
                if (!preg_match('/^\d+$/', $value)) {
                    $toRemove[] = $attr->name;
                }
            }
        }

        foreach ($toRemove as $name) {
            $node->removeAttribute($name);
        }

        if ($node->getAttribute('target') === '_blank' && !$node->hasAttribute('rel')) {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    }

    protected static function isSafeUrl(string $url, string $attr): bool
    {
        if ($url === '') {
            return true;
        }

        // Относительные пути, якоря, query-строки.
        if (str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, '?')) {
            return true;
        }

        // Обратная совместимость со старыми статьями, где картинки хранились как base64.
        if ($attr === 'src' && preg_match('#^data:image/[a-z0-9.+-]+;base64,#i', $url)) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return $scheme === '' || in_array($scheme, static::$allowedSchemes, true);
    }
}