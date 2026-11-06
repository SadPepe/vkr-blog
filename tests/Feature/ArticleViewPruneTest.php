<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArticleViewPruneTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_view_rows_are_pruned_and_recent_rows_kept(): void
    {
        $article = Article::factory()->create();

        $old = now()->subDays(400);
        $recent = now()->subHour();

        DB::table('article_views')->insert([
            [
                'article_id' => $article->id,
                'ip_address' => '10.0.0.1',
                'user_agent' => 'Old UA',
                'created_at' => $old,
                'updated_at' => $old,
            ],
            [
                'article_id' => $article->id,
                'ip_address' => '10.0.0.2',
                'user_agent' => 'Recent UA',
                'created_at' => $recent,
                'updated_at' => $recent,
            ],
        ]);

        $this->artisan('views:prune')->assertExitCode(0);

        $this->assertDatabaseMissing('article_views', ['ip_address' => '10.0.0.1']);
        $this->assertDatabaseHas('article_views', ['ip_address' => '10.0.0.2']);
    }

    public function test_all_time_popular_uses_views_count_without_detail_rows(): void
    {
        $article = Article::factory()->create(['views_count' => 42]);

        $popular = Article::popularForPeriod(5, 'all');

        $this->assertSame($article->id, $popular->first()->id);
    }

    public function test_week_popular_still_counts_recent_detail_rows(): void
    {
        $article = Article::factory()->create(['views_count' => 0]);

        DB::table('article_views')->insert([
            'article_id' => $article->id,
            'ip_address' => '10.0.0.3',
            'user_agent' => 'Recent UA',
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        $popular = Article::popularForPeriod(5, 'week');

        $this->assertSame($article->id, $popular->first()->id);
    }
}
