<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ArticlePinVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function pinUrl(Article $article): string
    {
        return route('articles.pin', ['article' => $article->slug]);
    }

    private function visibilityUrl(Article $article): string
    {
        return route('articles.visibility', ['article' => $article->slug]);
    }

    public function test_author_pins_own_article_and_new_pin_replaces_previous(): void
    {
        $author = User::factory()->create();
        $first = Article::factory()->create(['user_id' => $author->id]);
        $second = Article::factory()->create(['user_id' => $author->id]);

        $this->actingAs($author)->patch($this->pinUrl($first))->assertRedirect();

        $this->assertTrue($first->fresh()->is_pinned);
        $this->assertNotNull($first->fresh()->pinned_at);

        $this->actingAs($author)->patch($this->pinUrl($second))->assertRedirect();

        $this->assertFalse($first->fresh()->is_pinned);
        $this->assertNull($first->fresh()->pinned_at);
        $this->assertTrue($second->fresh()->is_pinned);
    }

    public function test_second_pin_same_article_unpins_it(): void
    {
        $author = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $author->id]);

        $this->actingAs($author)->patch($this->pinUrl($article));
        $this->actingAs($author)->patch($this->pinUrl($article));

        $this->assertFalse($article->fresh()->is_pinned);
        $this->assertNull($article->fresh()->pinned_at);
    }

    public function test_pinned_article_is_first_in_profile_and_author_page(): void
    {
        $author = User::factory()->create();
        $older = Article::factory()->create(['user_id' => $author->id]);
        $newer = Article::factory()->create(['user_id' => $author->id]);
        $older->update(['is_pinned' => true, 'pinned_at' => now()]);

        $this->actingAs($author)->get('/profile')->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('articles.data.0.id', $older->id));

        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('authors.show', $author->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Authors/Show')
                ->where('articles.data.0.id', $older->id)
                ->where('articles.data.1.id', $newer->id));
    }

    public function test_other_user_cannot_pin_or_hide_article(): void
    {
        $article = Article::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->patch($this->pinUrl($article))->assertForbidden();
        $this->actingAs($stranger)->patch($this->visibilityUrl($article))->assertForbidden();

        $this->assertFalse($article->fresh()->is_pinned);
        $this->assertTrue($article->fresh()->isVisible());
    }

    public function test_guest_redirected_to_login(): void
    {
        $article = Article::factory()->create();

        $this->patch($this->pinUrl($article))->assertRedirect(route('login'));
        $this->patch($this->visibilityUrl($article))->assertRedirect(route('login'));
    }

    public function test_hidden_article_is_unavailable_to_readers_but_open_to_author(): void
    {
        $author = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $author->id, 'is_pinned' => true, 'pinned_at' => now()]);

        $this->actingAs($author)->patch($this->visibilityUrl($article))->assertRedirect();

        $article->refresh();

        $this->assertSame(Article::STATUS_HIDDEN, $article->status);
        // Скрытый пост перестаёт быть закреплённым
        $this->assertFalse($article->is_pinned);
        $this->assertNull($article->pinned_at);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('articles.data', 0));

        // Чужой читатель скрытого поста не должен открыть ни страницу, ни ленивую подгрузку
        $reader = User::factory()->create();

        $this->actingAs($reader)->get('/articles/'.$article->slug)->assertNotFound();
        $this->actingAs($reader)
            ->get(route('articles.excerpt', ['article' => $article->slug]))
            ->assertNotFound();

        // Автор видит свой скрытый пост и не накручивает просмотры
        $this->actingAs($author)->get('/articles/'.$article->slug)->assertOk();
        $this->assertSame(0, (int) $article->fresh()->views_count);
    }

    public function test_hidden_article_is_closed_for_guest(): void
    {
        $article = Article::factory()->create(['status' => Article::STATUS_HIDDEN]);

        $this->get('/articles/'.$article->slug)->assertNotFound();
        $this->get(route('articles.excerpt', ['article' => $article->slug]))->assertNotFound();
    }

    public function test_author_publishes_hidden_article_again(): void
    {
        $author = User::factory()->create();
        $article = Article::factory()->create([
            'user_id' => $author->id,
            'status' => Article::STATUS_HIDDEN,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($author)->patch($this->visibilityUrl($article))->assertRedirect();

        $this->assertSame(Article::STATUS_PUBLISHED, $article->fresh()->status);
        $this->get('/articles/'.$article->slug)->assertOk();
        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('articles.data', 1));
    }

    public function test_hidden_article_cannot_be_pinned(): void
    {
        $author = User::factory()->create();
        $article = Article::factory()->create([
            'user_id' => $author->id,
            'status' => Article::STATUS_HIDDEN,
        ]);

        $this->actingAs($author)
            ->patch($this->pinUrl($article))
            ->assertSessionHas('error');

        $this->assertFalse($article->fresh()->is_pinned);
    }
    public function test_hidden_articles_live_in_separate_profile_tab(): void
    {
        $author = User::factory()->create();
        $published = Article::factory()->create(['user_id' => $author->id]);
        $hidden = Article::factory()->create([
            'user_id' => $author->id,
            'status' => Article::STATUS_HIDDEN,
        ]);

        $this->actingAs($author)->get('/profile')->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('tab', 'articles')
            ->where('counts.articles', 1)
            ->where('counts.hidden', 1)
            ->has('articles.data', 1)
            ->where('articles.data.0.id', $published->id));

        $this->actingAs($author)->get('/profile?tab=hidden')->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('tab', 'hidden')
            ->has('articles.data', 1)
            ->where('articles.data.0.id', $hidden->id));
    }
}
