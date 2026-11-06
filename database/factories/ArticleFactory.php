<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim((string) fake()->sentence(4), '.');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'excerpt' => fake()->sentence(8),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'card_image_path' => null,
            'user_id' => User::factory(),
            'category_slug' => null,
            'views_count' => 0,
            'likes_count' => 0,
            'comments_count' => 0,
        ];
    }
}
