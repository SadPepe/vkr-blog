<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;

class ArticleSeeder extends Seeder
{
    /**
     * Сколько статей создаём одним запуском.
     * Меняйте число под свои нужды.
     */
    public const ARTICLES_COUNT = 40;

    /**
     * Создаёт пачку тестовых статей на русском языке.
     *
     * Пример запуска:
     *   php artisan db:seed --class=ArticleSeeder
     */
    public function run(): void
    {
        $faker = fake('ru_RU');

        $user = User::orderBy('id')->first();
        if (!$user) {
            $user = User::factory()->create(['name' => 'Тестовый Автор']);
        }

        $categorySlugs = Category::pluck('slug')->all();

        for ($i = 0; $i < self::ARTICLES_COUNT; $i++) {
            $title = rtrim($faker->realText(70));

            $article = Article::create([
                'title' => $title,
                'slug' => $this->uniqueSlug($title),
                'excerpt' => rtrim($faker->realText(120)),
                'content' => $this->buildContent($faker),
                'card_image_path' => null,
                'user_id' => $user->id,
                'category_slug' => $categorySlugs ? $faker->randomElement($categorySlugs) : null,
                'views_count' => $faker->numberBetween(0, 600),
                'likes_count' => $faker->numberBetween(0, 80),
                'comments_count' => 0,
            ]);

            // Разносим даты публикации по последним трём месяцам,
            // чтобы «Популярное за неделю» и «Похожие» имели данные
            $article->created_at = $faker->dateTimeBetween('-3 months', 'now');
            $article->save();
        }
    }

    private function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    private function buildContent($faker): string
    {
        $paragraphs = ['<h2>' . rtrim($faker->realText(50)) . '</h2>'];

        $count = $faker->numberBetween(5, 9);
        for ($i = 0; $i < $count; $i++) {
            $paragraphs[] = '<p>' . rtrim($faker->realText($faker->numberBetween(180, 420))) . '</p>';
        }

        $paragraphs[] = '<blockquote>' . rtrim($faker->realText(120)) . '</blockquote>';
        $paragraphs[] = '<p>' . rtrim($faker->realText(200)) . '</p>';

        return implode("\n\n", $paragraphs);
    }
}