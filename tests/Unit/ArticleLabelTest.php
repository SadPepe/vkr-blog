<?php

namespace Tests\Unit;

use App\Models\Article;
use PHPUnit\Framework\TestCase;

/**
 * Афиша и рейлы показывают подпись у любого поста: заголовок есть не всегда,
 * а пост может состоять из одних картинок.
 */
class ArticleLabelTest extends TestCase
{
    public function test_title_wins_over_text(): void
    {
        $this->assertSame('Заголовок', Article::labelFor('Заголовок', 'Текст поста'));
    }

    public function test_text_is_used_when_title_is_missing(): void
    {
        $this->assertSame('Начало текста', Article::labelFor(null, '<p>Начало текста</p>'));
    }

    public function test_blank_title_is_treated_as_missing(): void
    {
        $this->assertSame('Текст', Article::labelFor('   ', 'Текст'));
    }

    public function test_photo_post_without_text_is_labelled(): void
    {
        $this->assertSame('Фотопост', Article::labelFor(null, ''));
        $this->assertSame('Фотопост', Article::labelFor(null, '<p><img src="/storage/images/1.jpg"></p>'));
    }

    public function test_label_is_trimmed_to_limit(): void
    {
        $label = Article::labelFor(null, str_repeat('а', 200), 40);

        $this->assertLessThanOrEqual(41, mb_strlen($label));
    }

    public function test_photo_post_detection(): void
    {
        $photo = new Article(['title' => null, 'excerpt' => '']);
        $text = new Article(['title' => null, 'excerpt' => 'Какой-то текст']);

        $this->assertTrue($photo->isPhotoPost());
        $this->assertFalse($text->isPhotoPost());
    }
}
