<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * Приводит HTML поста к абзацам.
 *
 * Вставленный из буфера текст редактор иногда сохраняет одним <p> со
 * строчными переносами <br>. На странице статьи такие строки выглядят
 * слипшимся полотном, поэтому делим абзац по <br> на отдельные <p>.
 */
class ContentNormalizer
{
    /** Возвращает HTML, в котором строки через <br> разведены по абзацам. */
    public static function paragraphs(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '' || ! preg_match('/<br\b/i', $html)) {
            return $html;
        }

        $previous = libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="UTF-8"?><html><body>' . $html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $body = $dom->getElementsByTagName('body')->item(0);

        if (! $body instanceof DOMElement) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return $html;
        }

        foreach (iterator_to_array($body->childNodes) as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'p') {
                self::splitByBreaks($child);
            }
        }

        $result = '';
        foreach ($body->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return trim($result);
    }

    /**
     * Делит <p> по прямым дочерним <br>: каждый непустой кусок становится
     * своим абзацем с теми же атрибутами (например, text-align).
     *
     * <br> внутри строчных тегов не трогаем: разрез прошёл бы через
     * <strong>/<em> и развалил разметку.
     */
    protected static function splitByBreaks(DOMElement $paragraph): void
    {
        $hasBreaks = false;
        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'br') {
                $hasBreaks = true;
                break;
            }
        }

        if (! $hasBreaks) {
            return;
        }

        $parent = $paragraph->parentNode;
        if (! $parent) {
            return;
        }

        /** @var array<int, array<int, \DOMNode>> $segments */
        $segments = [[]];
        foreach (iterator_to_array($paragraph->childNodes) as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'br') {
                $segments[] = [];

                continue;
            }
            $segments[array_key_last($segments)][] = $child;
        }

        // Кусок <br> в конце абзаца просто исчезает: пустые сегменты не создаём.
        foreach ($segments as $nodes) {
            if (self::isEmptySegment($nodes)) {
                continue;
            }

            $newParagraph = $paragraph->cloneNode(false);
            foreach ($nodes as $node) {
                $newParagraph->appendChild($node);
            }

            $parent->insertBefore($newParagraph, $paragraph);
        }

        // Исходный абзац опустел (его узлы переехали) — убираем.
        $parent->removeChild($paragraph);
    }

    /** @param array<int, \DOMNode> $nodes */
    protected static function isEmptySegment(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                $tag = strtolower($node->tagName);
                if (in_array($tag, ['img', 'hr', 'figure', 'br'], true)) {
                    return false;
                }
            }

            $text = trim(str_replace("\u{00A0}", ' ', $node->textContent));
            if ($text !== '') {
                return false;
            }
        }

        return true;
    }
}
