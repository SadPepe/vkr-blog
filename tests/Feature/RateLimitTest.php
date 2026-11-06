<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_creation_is_rate_limited(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['slug' => 'limit-comments']);

        // throttle:10,1 — десять комментариев в минуту разрешены
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)
                ->post(route('comments.store', $article->slug), ['content' => "Комментарий {$i}"])
                ->assertRedirect();
        }

        $this->actingAs($user)
            ->post(route('comments.store', $article->slug), ['content' => 'Одиннадцатый'])
            ->assertStatus(429);
    }

    public function test_article_likes_are_rate_limited(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['slug' => 'limit-likes']);

        // throttle:30,1 — тридцать переключений лайка в минуту разрешены
        for ($i = 0; $i < 30; $i++) {
            $this->actingAs($user)
                ->post(route('articles.like.toggle', $article->slug))
                ->assertOk();
        }

        $this->actingAs($user)
            ->post(route('articles.like.toggle', $article->slug))
            ->assertStatus(429);
    }
}
