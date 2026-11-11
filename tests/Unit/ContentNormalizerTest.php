<?php

namespace Tests\Unit;

use App\Support\ContentNormalizer;
use PHPUnit\Framework\TestCase;

class ContentNormalizerTest extends TestCase
{
    public function test_splits_lines_saved_as_breaks_into_paragraphs(): void
    {
        $html = '<p>Первая строка<br>Вторая строка<br>Третья</p>';

        $result = ContentNormalizer::paragraphs($html);

        $this->assertSame('<p>Первая строка</p><p>Вторая строка</p><p>Третья</p>', $result);
    }

    public function test_keeps_paragraph_attributes_on_every_part(): void
    {
        $html = '<p style="text-align: center" class="lead">Раз<br>Два</p>';

        $result = ContentNormalizer::paragraphs($html);

        $this->assertSame(2, substr_count($result, '<p'));
        $this->assertSame(2, substr_count($result, 'style="text-align: center"'));
        $this->assertSame(2, substr_count($result, 'class="lead"'));
    }

    public function test_drops_empty_lines_between_paragraphs(): void
    {
        $result = ContentNormalizer::paragraphs('<p>Абзац<br><br>Ещё абзац</p>');

        $this->assertSame('<p>Абзац</p><p>Ещё абзац</p>', $result);
    }

    public function test_does_not_touch_breaks_inside_inline_tags(): void
    {
        $html = '<p><strong>Строка<br>строка</strong></p>';

        $this->assertSame($html, ContentNormalizer::paragraphs($html));
    }

    public function test_returns_html_without_breaks_unchanged(): void
    {
        $html = '<p>Один</p><p>Два</p>';

        $this->assertSame($html, ContentNormalizer::paragraphs($html));
    }

    public function test_handles_empty_content(): void
    {
        $this->assertSame('', ContentNormalizer::paragraphs(''));
        $this->assertSame('', ContentNormalizer::paragraphs(null));
    }
}
