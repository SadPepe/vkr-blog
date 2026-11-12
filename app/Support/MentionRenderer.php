<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Превращает @username в ссылки на публичный профиль.
 *
 * Обрабатываются только текстовые узлы: ссылки внутри уже существующих
 * <a>, а также код в <code>/<pre> не трогаем.
 */
final class MentionRenderer
{
    private const PATTERN = '/(?<![\p{L}\p{N}_@])@([a-z0-9_]{3,32})(?![\p{L}\p{N}_])/iu';

    public static function render(string $html): string
    {
        if (trim($html) === '' || ! str_contains($html, '@')) {
            return $html;
        }

        libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="UTF-8"?><html><body>'.$html.'</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $body = $dom->getElementsByTagName('body')->item(0);

        if (! $body instanceof DOMElement) {
            libxml_clear_errors();

            return $html;
        }

        self::walk($body);
        libxml_clear_errors();

        $result = '';
        foreach ($body->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return trim($result);
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                if (self::shouldSkip($child->parentNode)) {
                    continue;
                }

                self::replaceMentions($child);
                continue;
            }

            if ($child->hasChildNodes()) {
                self::walk($child);
            }
        }
    }

    private static function shouldSkip(?DOMNode $parent): bool
    {
        return $parent instanceof DOMElement
            && in_array(strtolower($parent->tagName), ['a', 'code', 'pre'], true);
    }

    private static function replaceMentions(DOMText $node): void
    {
        $text = $node->nodeValue ?? '';
        $doc = $node->ownerDocument;

        if (! $doc instanceof DOMDocument || $text === '') {
            return;
        }

        preg_match_all(self::PATTERN, $text, $matches, PREG_OFFSET_CAPTURE);

        if ($matches[0] === []) {
            return;
        }

        $fragment = $doc->createDocumentFragment();
        $offset = 0;

        foreach ($matches[0] as $index => [$raw, $position]) {
            $username = $matches[1][$index][0];

            if ($position > $offset) {
                $fragment->appendChild($doc->createTextNode(substr($text, $offset, $position - $offset)));
            }

            $link = $doc->createElement('a');
            $link->setAttribute('href', route('authors.byUsername', mb_strtolower($username)));
            $link->setAttribute('class', 'mention');
            $link->appendChild($doc->createTextNode('@'.$username));
            $fragment->appendChild($link);

            $offset = $position + strlen($raw);
        }

        if ($offset < strlen($text)) {
            $fragment->appendChild($doc->createTextNode(substr($text, $offset)));
        }

        $node->parentNode?->replaceChild($fragment, $node);
    }
}
