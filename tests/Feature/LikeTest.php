<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_like(): void
    {
        $article = Article::factory()->create(['slug' => 'guest-like']);

        $this->post(route('articles.like.toggle', $article->slug))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('likes', 0);
    }

    public function test_like_toggles_and_counter_follows(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['slug' => 'like-toggle']);

        $like = $this->actingAs($user)->post(route('articles.like.toggle', $article->slug));

        $like->assertOk()->assertJson(['liked' => true, 'likes_count' => 1]);
        $this->assertDatabaseHas('likes', ['article_id' => $article->id, 'user_id' => $user->id]);
        $this->assertSame(1, $article->refresh()->likes_count);

        $unlike = $this->actingAs($user)->post(route('articles.like.toggle', $article->slug));

        $unlike->assertOk()->assertJson(['liked' => false, 'likes_count' => 0]);
        $this->assertDatabaseCount('likes', 0);
        $this->assertSame(0, $article->refresh()->likes_count);
    }
}
