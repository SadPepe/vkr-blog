<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_site_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_regular_user_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_moderator_opens_dashboard(): void
    {
        $this->actingAs(User::factory()->moderator()->create())
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_opens_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_opens_users_and_categories_resources(): void
    {
        \App\Models\Category::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/categories')->assertOk();
    }

    public function test_moderator_opens_articles_and_comments(): void
    {
        \App\Models\Article::factory()->create();
        \App\Models\Comment::factory()->create();
        $moderator = User::factory()->moderator()->create();

        $this->actingAs($moderator)->get('/admin/articles')->assertOk();
        $this->actingAs($moderator)->get('/admin/comments')->assertOk();
    }

    public function test_moderator_opens_reports_queue(): void
    {
        \App\Models\Report::create([
            'reporter_id' => User::factory()->create()->id,
            'reportable_type' => 'article',
            'reportable_id' => \App\Models\Article::factory()->create()->id,
            'reason' => \App\Models\Report::REASON_SPAM,
        ]);

        $this->actingAs(User::factory()->moderator()->create())
            ->get('/admin/reports')
            ->assertOk();
    }

    public function test_reports_page_shows_comment_text_and_its_post(): void
    {
        $article = \App\Models\Article::factory()->create(['title' => 'Пост про жалобы']);
        $comment = \App\Models\Comment::factory()->create([
            'article_id' => $article->id,
            'content' => 'Комментарий для жалобы',
        ]);

        \App\Models\Report::create([
            'reporter_id' => User::factory()->create()->id,
            'reportable_type' => 'comment',
            'reportable_id' => $comment->id,
            'reason' => \App\Models\Report::REASON_ABUSE,
        ]);

        $this->actingAs(User::factory()->moderator()->create())
            ->get('/admin/reports')
            ->assertOk()
            ->assertSee('Комментарий для жалобы')
            ->assertSee('Пост про жалобы');
    }

    public function test_moderator_cannot_open_users_resource(): void
    {
        $this->actingAs(User::factory()->moderator()->create())
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_blocked_moderator_loses_access(): void
    {
        $this->actingAs(User::factory()->moderator()->blocked()->create())
            ->get('/admin')
            ->assertForbidden();
    }
}
