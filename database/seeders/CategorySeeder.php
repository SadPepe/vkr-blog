<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
    /**
     * Run the database seeds.
     */
        $categories = [
            ['id' => 1, 'name' => 'Красота и стиль', 'slug' => 'beauty'],
            ['id' => 2, 'name' => 'Спорт', 'slug' => 'sport'],
            ['id' => 3, 'name' => 'Путешествия', 'slug' => 'travel'],
            ['id' => 4, 'name' => 'Еда', 'slug' => 'food'],
            ['id' => 5, 'name' => 'Наука', 'slug' => 'science'],
            ['id' => 6, 'name' => 'Технологии', 'slug' => 'tech'],
            ['id' => 7, 'name' => 'Искусство', 'slug' => 'art'],
            ['id' => 8, 'name' => 'Игры', 'slug' => 'games'],
        ];
          // Используем upsert, чтобы обновить данные, если id уже существуют, 
        // или вставить новые, если таблицы пусты
        DB::table('categories')->upsert($categories, ['id'], ['name', 'slug']);
    }
}
