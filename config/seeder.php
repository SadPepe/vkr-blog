<?php

return [
    // Папка с исходными фото для демо-статей. null — database/seeders/images.
    'images_dir' => env('SEEDER_IMAGES_DIR'),

    // Сколько демо-статей создавать. null — значение по умолчанию из ArticleSeeder.
    'articles_count' => env('SEEDER_ARTICLES_COUNT'),
];
