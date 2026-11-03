<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Article',
            'excerpt' => 'Короткое описание статьи',
            'content' => '<p>Текст статьи</p>',
        ], $overrides);
    }

    public function test_guest_cannot_create_article(): void
    {
        $this->post('/articles', $this->payload())->assertRedirect(route('login'));

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_author_can_create_article(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/articles', $this->payload());

        $article = Article::firstOrFail();

        $response->assertRedirect(route('articles.show', $article->slug));
        $this->assertSame($user->id, $article->user_id);
        $this->assertSame('Test Article', $article->title);
        $this->assertSame('test-article', $article->slug);
        $this->assertSame(0, $article->comments_count);
    }

    public function test_slug_is_unique_for_same_title(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/articles', $this->payload());
        $this->actingAs($user)->post('/articles', $this->payload());

        $this->assertSame(
            ['test-article', 'test-article-1'],
            Article::orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_html_is_sanitized_on_save(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/articles', $this->payload([
            'content' => '<p>ok</p><script>alert(1)</script><img src="x" onerror="alert(2)">',
        ]));

        $content = Article::firstOrFail()->content;

        $this->assertStringContainsString('<p>ok</p>', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('onerror', $content);
    }

    public function test_author_can_update_own_article(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->for($user)->create([
            'title' => 'Old Title',
            'slug' => 'old-title',
        ]);

        $response = $this->actingAs($user)->put(
            route('articles.update', $article->slug),
            $this->payload(['title' => 'New Title'])
        );

        $response->assertRedirect(route('profile'));

        $article->refresh();
        $this->assertSame('New Title', $article->title);
        $this->assertSame('new-title', $article->slug);
    }

    public function test_other_user_cannot_update_article(): void
    {
        $author = User::factory()->create();
        $stranger = User::factory()->create();
        $article = Article::factory()->for($author)->create(['slug' => 'someone-article']);

        $this->actingAs($stranger)
            ->put(route('articles.update', $article->slug), $this->payload(['title' => 'Hacked']))
            ->assertForbidden();

        $this->assertSame($article->title, $article->fresh()->title);
    }

    public function test_guest_cannot_update_article(): void
    {
        $article = Article::factory()->create(['slug' => 'guest-update']);

        $this->put(route('articles.update', $article->slug), $this->payload())
            ->assertRedirect(route('login'));
    }

    public function test_author_can_delete_own_article(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->for($user)->create(['slug' => 'to-delete']);

        $this->actingAs($user)
            ->delete(route('articles.destroy', $article->slug))
            ->assertRedirect(route('profile'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_other_user_cannot_delete_article(): void
    {
        $article = Article::factory()->create(['slug' => 'foreign-article']);
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->delete(route('articles.destroy', $article->slug))
            ->assertForbidden();

        $this->assertDatabaseHas('articles', ['id' => $article->id]);
    }

    public function test_post_without_title_is_allowed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/articles', [
            'content' => '<p>Просто короткая мысль без заголовка</p>',
        ]);

        $article = Article::firstOrFail();

        $this->assertNull($article->title);
        $this->assertSame('Просто короткая мысль без заголовка', $article->excerpt);
        // Slug собирается из текста: только латиница, цифры и дефисы
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $article->slug);

        $this->get(route('articles.show', $article->slug))->assertOk();
    }

    public function test_image_only_post_uses_first_image_as_preview(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/articles', [
            'content' => '<p><img src="/storage/images/picture.jpg" alt="Фото"></p>',
        ]);

        $article = Article::firstOrFail();

        $this->assertNull($article->title);
        $this->assertSame('', $article->excerpt);
        $this->assertSame('/storage/images/picture.jpg', $article->card_image_path);
        $this->assertStringStartsWith('post-', $article->slug);

        $this->get(route('articles.show', $article->slug))->assertOk();
    }

    public function test_empty_post_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/articles', ['content' => '<p>   </p>'])
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_post_without_title_is_shown_in_feed(): void
    {
        $user = User::factory()->create();
        Article::factory()->for($user)->create([
            'title' => null,
            'slug' => 'kakaya-to-mysl',
            'excerpt' => 'Какая-то мысль на бегу',
            'content' => '<p>Какая-то мысль на бегу</p>',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->has('articles.data', 1)
                ->where('articles.data.0.title', null)
                ->where('articles.data.0.excerpt', 'Какая-то мысль на бегу')
                ->where('articles.data.0.preview', '<p>Какая-то мысль на бегу</p>')
                ->missing('articles.data.0.content')
                ->etc());
    }
}
