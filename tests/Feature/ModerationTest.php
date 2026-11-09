<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_moderators_and_admins_have_moderate_gate(): void
    {
        $this->assertFalse(User::factory()->create()->can('moderate'));
        $this->assertTrue(User::factory()->moderator()->create()->can('moderate'));
        $this->assertTrue(User::factory()->admin()->create()->can('moderate'));
    }

    public function test_moderator_hides_foreign_article_but_cannot_edit_or_delete_it(): void
    {
        $article = Article::factory()->create();
        $moderator = User::factory()->moderator()->create();

        $this->assertTrue($moderator->can('hide', $article));
        $this->assertFalse($moderator->can('update', $article));
        $this->assertFalse($moderator->can('delete', $article));
    }

    public function test_admin_deletes_foreign_article_but_still_cannot_edit_it(): void
    {
        $article = Article::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->can('delete', $article));
        $this->assertFalse($admin->can('update', $article));
    }

    public function test_author_keeps_rights_on_own_article(): void
    {
        $author = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $author->id]);

        $this->assertTrue($author->can('update', $article));
        $this->assertTrue($author->can('delete', $article));
        $this->assertFalse($author->can('hide', $article));
    }

    public function test_moderator_hides_comment_and_admin_deletes_it(): void
    {
        $comment = Comment::factory()->create();
        $moderator = User::factory()->moderator()->create();
        $admin = User::factory()->admin()->create();
        $stranger = User::factory()->create();

        $this->assertTrue($moderator->can('hide', $comment));
        $this->assertFalse($moderator->can('delete', $comment));
        $this->assertTrue($admin->can('delete', $comment));
        $this->assertFalse($stranger->can('hide', $comment));
    }

    public function test_only_admin_manages_users_and_categories(): void
    {
        $admin = User::factory()->admin()->create();
        $moderator = User::factory()->moderator()->create();
        $victim = User::factory()->create();

        $this->assertTrue($admin->can('update', $victim));
        $this->assertFalse($admin->can('update', $admin));
        $this->assertFalse($moderator->can('update', $victim));
        $this->assertFalse($admin->can('delete', $victim));
    }

    public function test_blocked_user_cannot_log_in(): void
    {
        User::factory()->blocked()->create(['username' => 'blocked']);

        $this->post('/login', ['username' => 'blocked', 'password' => 'password'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_visible_scope_hides_drafts_and_hidden_articles(): void
    {
        Article::factory()->create(['status' => Article::STATUS_PUBLISHED]);
        Article::factory()->create(['status' => Article::STATUS_HIDDEN]);
        Article::factory()->create(['status' => Article::STATUS_DRAFT]);

        $this->assertSame(1, Article::visible()->count());
    }

    public function test_visible_scope_hides_moderated_comments(): void
    {
        Comment::factory()->create();
        Comment::factory()->create(['hidden_at' => now()]);

        $this->assertSame(1, Comment::visible()->count());
    }

    public function test_hidden_article_disappears_from_feed_and_hides_from_guest(): void
    {
        $published = Article::factory()->create(['status' => Article::STATUS_PUBLISHED]);
        $hidden = Article::factory()->create([
            'status' => Article::STATUS_HIDDEN,
            'title' => 'Скрытый пост про модерацию',
        ]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('articles.data', 1)
            ->where('articles.data.0.id', $published->id));

        $this->get('/articles/'.$hidden->slug)->assertNotFound();

        $this->get('/search?query=модерацию')->assertInertia(fn (Assert $page) => $page
            ->component('SearchResults')
            ->has('articles.data', 0));

        $this->actingAs(User::factory()->moderator()->create())
            ->get('/articles/'.$hidden->slug)
            ->assertOk();
    }

    public function test_hidden_comment_is_not_rendered_on_article_page(): void
    {
        $article = Article::factory()->create();
        Comment::factory()->create(['article_id' => $article->id, 'content' => 'Видимый комментарий']);

        $this->get('/articles/'.$article->slug)->assertInertia(fn (Assert $page) => $page
            ->component('Articles/Show')
            ->has('article.comments', 1));

        Comment::query()->update(['hidden_at' => now()]);

        $this->get('/articles/'.$article->slug)->assertInertia(fn (Assert $page) => $page
            ->component('Articles/Show')
            ->has('article.comments', 0));
    }

    public function test_announcements_scope_respects_switch_and_dates(): void
    {
        Announcement::create(['body' => 'Активное', 'is_active' => true]);
        Announcement::create(['body' => 'Выключенное', 'is_active' => false]);
        Announcement::create(['body' => 'Просроченное', 'is_active' => true, 'ends_at' => now()->subDay()]);
        Announcement::create(['body' => 'Будущее', 'is_active' => true, 'starts_at' => now()->addDay()]);

        $bodies = Announcement::active()->ordered()->pluck('body')->all();

        $this->assertSame(['Активное'], $bodies);
    }
}
