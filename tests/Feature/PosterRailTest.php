<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Афиша: у поста всегда есть подпись и признак фотопоста,
 * даже если заголовка нет или в посте только картинка.
 */
class PosterRailTest extends TestCase
{
    use RefreshDatabase;

    public function test_poster_rail_gives_every_post_a_label(): void
    {
        $user = User::factory()->create();

        $untitled = Article::factory()->for($user)->create([
            'title' => null,
            'excerpt' => 'Начало текста без заголовка',
            'content' => '<p>Начало текста без заголовка</p>',
            'views_count' => 100,
            'status' => Article::STATUS_PUBLISHED,
        ]);

        $photo = Article::factory()->for($user)->create([
            'title' => null,
            'excerpt' => '',
            'content' => '<p><img src="/storage/images/poster.jpg"></p>',
            'card_image_path' => '/storage/images/poster.jpg',
            'views_count' => 50,
            'status' => Article::STATUS_PUBLISHED,
        ]);

        $titled = Article::factory()->for($user)->create([
            'title' => 'Пост с заголовком',
            'views_count' => 10,
            'status' => Article::STATUS_PUBLISHED,
        ]);

        $rail = Article::popularForPeriod(5, 'all')->keyBy('id');

        $this->assertSame('Начало текста без заголовка', $rail[$untitled->id]->label);
        $this->assertFalse($rail[$untitled->id]->is_photo_post);
        $this->assertSame('Пост с заголовком', $rail[$titled->id]->label);

        // Фотопост: подписи нет, поэтому отдаём миниатюру и метку
        $this->assertSame('Фотопост', $rail[$photo->id]->label);
        $this->assertTrue($rail[$photo->id]->is_photo_post);
        $this->assertSame('/storage/images/poster.jpg', $rail[$photo->id]->card_image_path);
    }
}
