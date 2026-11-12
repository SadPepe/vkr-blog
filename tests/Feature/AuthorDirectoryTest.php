<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthorDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_lists_only_authors_with_published_articles(): void
    {
        $publishedAuthor = User::factory()->create(['name' => 'Активный автор']);
        Article::factory()->for($publishedAuthor)->create(['status' => Article::STATUS_PUBLISHED]);

        $draftAuthor = User::factory()->create(['name' => 'Черновой автор']);
        Article::factory()->for($draftAuthor)->create(['status' => Article::STATUS_DRAFT]);

        $this->get(route('authors.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Authors/Index')
                ->has('authors.data', 1)
                ->where('authors.data.0.name', 'Активный автор')
                ->where('authors.data.0.articles_count', 1)
                ->where('filters.sort', 'active'));
    }

    public function test_directory_can_be_searched_by_name(): void
    {
        $found = User::factory()->create(['name' => 'Анна Петрова']);
        Article::factory()->for($found)->create();

        $other = User::factory()->create(['name' => 'Илья К.']);
        Article::factory()->for($other)->create();

        $this->get(route('authors.index', ['q' => 'Анна']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('authors.data', 1)
                ->where('authors.data.0.name', 'Анна Петрова'));
    }

    public function test_directory_search_is_case_insensitive_for_cyrillic_on_pgsql(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Регистронезависимый поиск по кириллице требует PostgreSQL (ilike)');
        }

        $author = User::factory()->create(['name' => 'КИРИЛЛИЦА В ВЕРХНЕМ РЕГИСТРЕ']);
        Article::factory()->for($author)->create();

        $this->get(route('authors.index', ['q' => 'кириллица']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('authors.data', 1)
                ->where('authors.data.0.name', 'КИРИЛЛИЦА В ВЕРХНЕМ РЕГИСТРЕ'));
    }

    public function test_directory_excludes_blocked_authors(): void
    {
        $blocked = User::factory()->blocked()->create();
        Article::factory()->for($blocked)->create();

        $this->get(route('authors.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('authors.data', 0));
    }

    public function test_home_rail_contains_active_authors(): void
    {
        $author = User::factory()->create(['name' => 'Депо 404']);
        Article::factory()->for($author)->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('activeAuthors', 1)
                ->where('activeAuthors.0.name', 'Депо 404'));
    }

    public function test_username_profile_route_opens_author_page(): void
    {
        $author = User::factory()->create(['username' => 'depo_404']);
        Article::factory()->for($author)->create();

        $this->get(route('authors.byUsername', 'depo_404'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Authors/Show')
                ->where('author.username', 'depo_404'));
    }

    public function test_mention_search_returns_username_and_name(): void
    {
        User::factory()->create(['name' => 'Анна Петрова', 'username' => 'anna_p']);

        $this->getJson(route('mentions.search', ['q' => 'anna']))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Анна Петрова',
                'username' => 'anna_p',
            ]);
    }
}
