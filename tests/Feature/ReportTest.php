<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_report(): void
    {
        $article = Article::factory()->create();

        $this->post('/reports', [
            'reportable_type' => 'article',
            'reportable_id' => $article->id,
            'reason' => Report::REASON_SPAM,
        ])->assertRedirect('/login');

        $this->assertSame(0, Report::count());
    }

    public function test_user_reports_article_and_comment(): void
    {
        $article = Article::factory()->create();
        $comment = Comment::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/reports', [
            'reportable_type' => 'article',
            'reportable_id' => $article->id,
            'reason' => Report::REASON_SPAM,
            'comment' => 'Реклама в тексте',
        ])->assertSessionHas('success');

        $this->actingAs($user)->post('/reports', [
            'reportable_type' => 'comment',
            'reportable_id' => $comment->id,
            'reason' => Report::REASON_ABUSE,
        ])->assertSessionHas('success');

        $this->assertSame(2, Report::count());
        $this->assertSame('article', Report::first()->reportable_type);
    }

    public function test_own_content_cannot_be_reported(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post('/reports', [
            'reportable_type' => 'article',
            'reportable_id' => $article->id,
            'reason' => Report::REASON_SPAM,
        ])->assertSessionHas('error');

        $this->assertSame(0, Report::count());
    }

    public function test_duplicate_pending_report_is_blocked(): void
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $payload = [
            'reportable_type' => 'article',
            'reportable_id' => $article->id,
            'reason' => Report::REASON_ADS,
        ];

        $this->actingAs($user)->post('/reports', $payload)->assertSessionHas('success');
        $this->actingAs($user)->post('/reports', $payload)->assertSessionHas('error');

        $this->assertSame(1, Report::count());
    }

    public function test_unknown_type_and_reason_are_rejected(): void
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/reports', [
            'reportable_type' => 'user',
            'reportable_id' => $user->id,
            'reason' => Report::REASON_SPAM,
        ])->assertSessionHasErrors('reportable_type');

        $this->actingAs($user)->post('/reports', [
            'reportable_type' => 'article',
            'reportable_id' => $article->id,
            'reason' => 'выдуманная причина',
        ])->assertSessionHasErrors('reason');
    }
}
