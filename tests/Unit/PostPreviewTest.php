<?php

namespace Tests\Unit;

use App\Support\PostPreview;
use PHPUnit\Framework\TestCase;

class PostPreviewTest extends TestCase
{
    public function test_preview_keeps_author_order_of_text_and_images(): void
    {
        $html = '<p><img src="/storage/images/first.jpg"></p><p>Текст после фото</p>';

        $preview = PostPreview::make($html);

        $this->assertStringContainsString('<img', $preview);
        $this->assertStringContainsString('Текст после фото', $preview);
        // Картинка идёт раньше текста — как в посте
        $this->assertLessThan(mb_strpos($preview, 'Текст после фото'), mb_strpos($preview, '<img'));
    }

    public function test_preview_is_limited_by_images_count(): void
    {
        $html = '<p><img src="/storage/images/1.jpg"></p>'
            .'<p><img src="/storage/images/2.jpg"></p>'
            .'<p><img src="/storage/images/3.jpg"></p>';

        $preview = PostPreview::make($html);

        $this->assertSame(PostPreview::IMAGE_LIMIT, substr_count($preview, '<img'));
    }

    public function test_oversized_single_paragraph_is_truncated(): void
    {
        $html = '<p>'.str_repeat('Слово ', 500).'</p>';

        $preview = PostPreview::make($html);

        $this->assertLessThan(mb_strlen(strip_tags($html)), mb_strlen(strip_tags($preview)));
        $this->assertStringEndsWith('…', strip_tags($preview));
    }

    public function test_excerpt_is_complete_for_short_post(): void
    {
        $excerpt = PostPreview::excerpt('<p>Короткий пост</p>');

        $this->assertTrue($excerpt['complete']);
        $this->assertStringContainsString('Короткий пост', $excerpt['html']);
    }

    public function test_excerpt_returns_more_text_than_feed_preview(): void
    {
        $html = str_repeat('<p>'.str_repeat('Слово ', 90).'</p>', 20);

        $preview = PostPreview::make($html);
        $excerpt = PostPreview::excerpt($html);

        $this->assertFalse($excerpt['complete']);
        $this->assertGreaterThan(
            mb_strlen(strip_tags($preview)) * 2,
            mb_strlen(strip_tags($excerpt['html']))
        );
        $this->assertStringContainsString('<p>', $excerpt['html']);
    }

    public function test_excerpt_stays_within_its_limit(): void
    {
        $html = str_repeat('<p>'.str_repeat('Слово ', 90).'</p>', 200);

        $excerpt = PostPreview::excerpt($html);

        $this->assertFalse($excerpt['complete']);
        $this->assertLessThanOrEqual(
            PostPreview::EXCERPT_TEXT_LIMIT + 600,
            mb_strlen(strip_tags($excerpt['html']))
        );
    }

    public function test_collapsed_preview_is_longer_than_a_teaser_but_bounded(): void
    {
        $html = str_repeat('<p>'.str_repeat('Слово ', 90).'</p>', 20);

        $preview = PostPreview::make($html);

        $this->assertLessThanOrEqual(PostPreview::TEXT_LIMIT + 10, mb_strlen(strip_tags($preview)));
        $this->assertGreaterThan(PostPreview::TEXT_LIMIT / 2, mb_strlen(strip_tags($preview)));
    }
}
