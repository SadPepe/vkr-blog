<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_article_by_title(): void
    {
        Article::factory()->create(['title' => 'Laravel Testing Basics']);
        Article::factory()->create(['title' => 'Совсем другая статья']);

        $this->get('/search?query=Testing')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SearchResults')
                ->has('articles.data', 1)
                ->where('articles.data.0.title', 'Laravel Testing Basics')
                ->where('searchQuery', 'Testing'));
    }

    public function test_search_returns_empty_page_when_nothing_matches(): void
    {
        Article::factory()->create(['title' => 'Laravel Testing Basics']);

        $this->get('/search?query=несуществующийтекст')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SearchResults')
                ->has('articles.data', 0));
    }

    public function test_search_is_case_insensitive_for_cyrillic(): void
    {
        // sqlite без ICU регистронезависим только для латиницы; требование
        // проверяется на PostgreSQL (ilike), на котором работает сайт
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Регистронезависимый поиск по кириллице требует PostgreSQL (ilike)');
        }

        Article::factory()->create(['title' => 'КИРИЛЛИЦА В ВЕРХНЕМ РЕГИСТРЕ']);

        $this->get('/search?query=кириллица')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('articles.data', 1));
    }
}
