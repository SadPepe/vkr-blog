<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;

class ArticleSeeder extends Seeder
{
    /**
     * Сколько статей должно быть в базе после запуска.
     * Меняйте число под свои нужды.
     */
    public const ARTICLES_COUNT = 40;

    /**
     * Откуда берём картинки для превью и вставок в текст.
     * Можно переопределить через SEEDER_IMAGES_DIR в .env.
     */
    public const IMAGES_DIR = '/home/anna/Изображения/Wallpapers/Best';

    /**
     * Сколько фотографий берём из папки.
     * 0 — брать все файлы из папки.
     */
    public const IMAGES_COUNT = 0;

    /**
     * Префикс имён скопированных файлов, чтобы не затирать загруженные вручную.
     */
    private const SEED_IMAGE_PREFIX = 'seed-';

    /**
     * Подписи под иллюстрациями — для демонстрации <figcaption>.
     */
    private const IMAGE_CAPTIONS = [
        'Фотография из подборки',
        'Иллюстрация к материалу',
        'Кадр из архива редакции',
        'Фото: личный архив автора',
    ];

    /** @var array<int, string>|null Кэш предложений русского корпуса. */
    private ?array $sentences = null;

    /** @var array<int, array{card: string, post: string}> Колода фото на этот запуск. */
    private array $imageDeck = [];

    /** @var int Позиция в колоде фото. */
    private int $imageDeckIndex = 0;

    /**
     * Создаёт тестовые статьи на русском языке.
     *
     * Каждая статья — «витрина» возможностей редактора: заголовки h2/h3,
     * маркированный список с вложенным, нумерованный список со своим
     * началом, цитата, блок кода, горизонтальная линия, ссылка, жирный /
     * курсив / подчёркивание / зачёркивание / код в строке, выравнивание
     * по центру и 2–3 фотографии с подписями.
     *
     * Повторный запуск не плодит дубликаты: уже засеянные статьи
     * (у них превью начинается с «seed-») получают новый текст и новые
     * фотографии, а недостающие до ARTICLES_COUNT статьи создаются.
     * Количество можно переопределить через SEEDER_ARTICLES_COUNT в .env.
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
        $images = $this->prepareImages();

        if (!$images) {
            $this->command?->warn('Картинки не найдены, статьи будут без изображений.');
        }

        // Колода фото на этот запуск: идём по ней по кругу, чтобы каждое
        // фото из папки гарантированно попало хотя бы в одну статью.
        $this->imageDeck = $images;
        shuffle($this->imageDeck);
        $this->imageDeckIndex = 0;

        // env() возвращает null при закешированном конфиге — тогда берём значение по умолчанию.
        $configured = env('SEEDER_ARTICLES_COUNT');
        $limit = ($configured === null || $configured === '')
            ? self::ARTICLES_COUNT
            : max(0, (int) $configured);

        // Статьи, созданные этим сидером (у них превью — скопированный файл).
        $seeded = Article::query()
            ->where('card_image_path', 'like', '%' . self::SEED_IMAGE_PREFIX . '%')
            ->orderBy('id')
            ->get();

        // Освежаем текст и фото уже существующих статей.
        foreach ($seeded as $article) {
            $picked = $this->pickImages($faker);

            if ($picked) {
                $article->card_image_path = $picked[0]['card'];
            }

            $article->content = $this->buildContent($faker, $picked);
            $article->save();
        }

        // Досоздаём статьи, если их меньше заданного количества.
        $toCreate = max(0, $limit - $seeded->count());

        for ($i = 0; $i < $toCreate; $i++) {
            $title = $this->title($faker);
            $picked = $this->pickImages($faker);

            $article = Article::create([
                'title' => $title,
                'slug' => $this->uniqueSlug($title),
                'excerpt' => $this->sentence($faker, 120),
                'content' => $this->buildContent($faker, $picked),
                'card_image_path' => $picked[0]['card'] ?? null,
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

        $this->command?->info(sprintf(
            'Статьи: обновлено %d, создано %d.',
            $seeded->count(),
            $toCreate
        ));
    }

    /**
     * Копирует фотографии из папки в публичный диск и возвращает пути.
     *
     * По умолчанию берутся все файлы папки; порядок перемешивается,
     * а фото внутри статьи выбираются отдельно для каждой статьи.
     *
     * @return array<int, array{card: string, post: string}>
     */
    private function prepareImages(): array
    {
        $sourceDir = env('SEEDER_IMAGES_DIR', self::IMAGES_DIR);

        if (!is_dir($sourceDir)) {
            $this->command?->warn("Папка с картинками не найдена: {$sourceDir}");
            return [];
        }

        $files = [];
        foreach (scandir($sourceDir) ?: [] as $file) {
            $path = $sourceDir . DIRECTORY_SEPARATOR . $file;
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (is_file($path) && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $files[] = $file;
            }
        }

        shuffle($files);

        if (self::IMAGES_COUNT > 0) {
            $files = array_slice($files, 0, self::IMAGES_COUNT);
        }

        $disk = Storage::disk('public');
        $images = [];
        $used = [];

        foreach ($files as $file) {
            $name = self::SEED_IMAGE_PREFIX . $this->safeImageName($file);

            // Одинаковые «безопасные» имена не должны затирать друг друга.
            if (isset($used[$name])) {
                $name = self::SEED_IMAGE_PREFIX . $this->safeImageName($file, md5($file));
            }
            $used[$name] = true;

            $source = $sourceDir . DIRECTORY_SEPARATOR . $file;

            // Одна и та же картинка используется и как превью, и как вставка в тексте.
            foreach (['article-cards', 'images'] as $directory) {
                if (!$disk->exists($directory . '/' . $name)) {
                    $disk->put($directory . '/' . $name, file_get_contents($source));
                }
            }

            $images[] = [
                'card' => '/storage/article-cards/' . $name,
                'post' => '/storage/images/' . $name,
            ];
        }

        return $images;
    }

    /**
     * Имя файла для публичного диска: без кавычек, пробелов и прочих
     * символов, которые ломают URL и регулярки очистки картинок.
     */
    private function safeImageName(string $file, string $suffix = ''): string
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $base = pathinfo($file, PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9_-]+/u', '-', $base);
        $base = trim($base, '-_');

        if ($base === '') {
            $base = 'image';
        }

        if ($suffix !== '') {
            $base .= '-' . $suffix;
        }

        return $base . '.' . $extension;
    }

    /**
     * Выбирает фото для одной статьи: первое — превью, остальные (2–3) —
     * иллюстрации внутри текста.
     *
     * Фото берутся из «колоды» по кругу, поэтому все файлы папки
     * используются, а внутри статьи порядок перемешивается.
     *
     * @return array<int, array{card: string, post: string}>
     */
    private function pickImages($faker): array
    {
        if (!$this->imageDeck) {
            return [];
        }

        $count = 1 + $faker->numberBetween(2, 3);
        $picked = [];

        for ($i = 0; $i < $count; $i++) {
            $picked[] = $this->imageDeck[$this->imageDeckIndex % count($this->imageDeck)];
            $this->imageDeckIndex++;
        }

        shuffle($picked);

        return $picked;
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

    /**
     * Собирает HTML статьи со всеми блоками редактора.
     *
     * @param array<int, array{card: string, post: string}> $picked
     */
    private function buildContent($faker, array $picked = []): string
    {
        $blocks = [
            '<p>' . $this->esc($this->sentence($faker, 280)) . '</p>',
            $this->inlineFormatting($faker),

            '<h2>' . $this->esc($this->heading($faker, 30, 70)) . '</h2>',
            '<p>' . $this->esc($this->sentence($faker, 220)) . '</p>',
            $this->bulletList($faker),

            '<h3>' . $this->esc($this->heading($faker, 25, 60)) . '</h3>',
            $this->orderedList($faker),
            $this->blockquote($faker),
            $this->centeredNote($faker),

            '<h2>' . $this->esc($this->heading($faker, 30, 70)) . '</h2>',
            '<p>' . $this->esc($this->sentence($faker, 200)) . '</p>',
            $this->codeBlock(),
            '<p>' . $this->esc($this->sentence($faker, 180)) . ' '
                . 'Полезные материалы ищите в <a href="https://laravel.com/docs/queries" target="_blank" rel="noopener noreferrer">документации Laravel</a>.</p>',
            '<hr>',

            '<h3>' . $this->esc($this->heading($faker, 25, 60)) . '</h3>',
            '<p>' . $this->esc($this->sentence($faker, 240)) . '</p>',
        ];

        // Превью уже стоит в шапке — внутри текста используем остальные фото.
        if ($picked) {
            $blocks = $this->insertImages($blocks, array_slice($picked, 1), $faker);
        }

        return implode("\n\n", $blocks);
    }

    /**
     * Абзац со всем строчным форматированием редактора:
     * жирный, курсив, подчёркивание, зачёркивание, код и ссылка.
     */
    private function inlineFormatting($faker): string
    {
        return '<p>'
            . $this->esc($this->sentence($faker, 70)) . ' '
            . '<strong>' . $this->esc($this->sentence($faker, 55)) . '</strong> '
            . $this->esc($this->sentence($faker, 60)) . ' '
            . '<em>' . $this->esc($this->sentence($faker, 50)) . '</em> '
            . '<u>' . $this->esc($this->sentence($faker, 45)) . '</u> '
            . '<s>' . $this->esc($this->sentence($faker, 45)) . '</s> '
            . 'Команда для запуска: <code>php artisan migrate --seed</code>.'
            . '</p>';
    }

    /**
     * Маркированный список с вложенным подсписком.
     */
    private function bulletList($faker): string
    {
        $items = [];
        $total = $faker->numberBetween(3, 4);

        for ($i = 0; $i < $total; $i++) {
            $items[] = '<li><p>' . $this->esc($this->sentence($faker, $faker->numberBetween(50, 110))) . '</p></li>';
        }

        $nested = [];
        for ($i = 0; $i < 2; $i++) {
            $nested[] = '<li><p>' . $this->esc($this->sentence($faker, 60)) . '</p></li>';
        }

        $index = min(1, count($items) - 1);
        $items[$index] = '<li><p>'
            . $this->esc($this->sentence($faker, 70))
            . '</p><ul>' . implode('', $nested) . '</ul></li>';

        return '<ul>' . implode('', $items) . '</ul>';
    }

    /**
     * Нумерованный список, который начинается не с единицы.
     */
    private function orderedList($faker): string
    {
        $start = $faker->numberBetween(2, 7);
        $items = [];

        for ($i = 0; $i < 4; $i++) {
            $items[] = '<li><p>' . $this->esc($this->sentence($faker, $faker->numberBetween(50, 100))) . '</p></li>';
        }

        return '<ol start="' . $start . '">' . implode('', $items) . '</ol>';
    }

    private function blockquote($faker): string
    {
        return '<blockquote><p>' . $this->esc($this->sentence($faker, 170)) . '</p></blockquote>';
    }

    /**
     * Абзац с выравниванием по центру — редактор пишет это в style.
     */
    private function centeredNote($faker): string
    {
        return '<p style="text-align: center;"><em>' . $this->esc($this->sentence($faker, 120)) . '</em></p>';
    }

    /**
     * Блок кода: ровно то, что сохраняет TipTap (pre > code.language-php).
     */
    private function codeBlock(): string
    {
        $code = <<<'PHP'
$articles = Article::query()
    ->with('user', 'category')
    ->latest()
    ->take(5)
    ->get();

foreach ($articles as $article) {
    echo $article->title . PHP_EOL;
}
PHP;

        return '<pre><code class="language-php">'
            . htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '</code></pre>';
    }

    /**
     * Заголовок: целое короткое предложение из корпуса, без обрывов на полуслове.
     */
    private function title($faker): string
    {
        return $this->heading($faker, 35, 90);
    }

    /**
     * Заголовок нужной длины из корпуса или, если подходящего нет, из текста.
     */
    private function heading($faker, int $min, int $max): string
    {
        $candidates = array_values(array_filter(
            $this->russianSentences(),
            fn ($sentence) => mb_strlen($sentence) >= $min
                && mb_strlen($sentence) <= $max
                && !preg_match('/^[«»"—–]/u', $sentence)
        ));

        if ($candidates) {
            return rtrim($faker->randomElement($candidates), '.');
        }

        return rtrim($this->truncateAtWord($this->text($faker, $max), $max), '.');
    }

    /**
     * Готовый к вставке в HTML фрагмент: с заглавной буквы и точкой в конце.
     */
    private function sentence($faker, int $chars): string
    {
        $text = $this->text($faker, $chars);
        $text = preg_replace('/^[\s—–-]+/u', '', $text);

        if ($text === '') {
            return $text;
        }

        $text = mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);

        // Реплики из корпуса обрываются на полуслове — ставим точку,
        // если знака конца предложения нет.
        if (!preg_match('/[.!?…]$/u', $text)) {
            $text = preg_replace('/[\s,;:—–-]+$/u', '', $text) . '.';
        }

        return $text;
    }

    /**
     * Осмысленный русский текст нужной длины.
     * realText в Faker ru_RU требует расширение iconv, поэтому есть запасной путь.
     */
    private function text($faker, int $chars): string
    {
        if (function_exists('iconv')) {
            return rtrim($faker->realText($chars));
        }

        $sentences = $this->russianSentences();
        $text = '';

        while (mb_strlen($text) < $chars) {
            $text .= ($text === '' ? '' : ' ') . $faker->randomElement($sentences);
        }

        $text = $this->truncateAtWord($text, $chars);

        // Реплики из корпуса часто начинаются с тире — в тексте статьи оно лишнее.
        return preg_replace('/^[\s—–-]+/u', '', $text);
    }

    /**
     * Экранирует текст, чтобы кавычки и амперсанды не ломали HTML.
     */
    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Отрезает текст по границе слова, чтобы не рвать слово пополам.
     */
    private function truncateAtWord(string $text, int $chars): string
    {
        if (mb_strlen($text) <= $chars) {
            return $text;
        }

        $cut = mb_substr($text, 0, $chars);
        $space = mb_strrpos($cut, ' ');
        $cut = $space ? mb_substr($cut, 0, $space) : $cut;

        // rtrim() работает с байтами: многобайтовый «—» в charlist рвёт UTF-8.
        return preg_replace('/[\s,;:—–-]+$/u', '', $cut);
    }

    /**
     * Предложения из русского корпуса Faker (по умолчанию — «Мёртвые души»).
     *
     * @return array<int, string>
     */
    private function russianSentences(): array
    {
        if ($this->sentences !== null) {
            return $this->sentences;
        }

        $baseText = (new \ReflectionClass(\Faker\Provider\ru_RU\Text::class))
            ->getStaticPropertyValue('baseText');

        $normalized = preg_replace('/\s+/u', ' ', $baseText);
        $parts = preg_split('/(?<=[.!?])\s+/u', $normalized) ?: [];

        $this->sentences = array_values(array_filter(
            array_map('trim', $parts),
            fn ($sentence) => mb_strlen($sentence) > 40
        ));

        return $this->sentences;
    }

    /**
     * Вставляет картинки после случайных абзацев, порядок каждый раз новый.
     *
     * @param array<int, string> $blocks
     * @param array<int, array{card: string, post: string}> $images
     * @return array<int, string>
     */
    private function insertImages(array $blocks, array $images, $faker): array
    {
        $paragraphIndexes = array_keys(array_filter(
            $blocks,
            fn ($block) => str_starts_with($block, '<p')
        ));

        if (!$paragraphIndexes || !$images) {
            return $blocks;
        }

        $images = array_values($images);
        shuffle($images);

        $count = min(count($images), count($paragraphIndexes));
        $targets = $faker->randomElements($paragraphIndexes, $count);
        sort($targets);

        // Идём с конца, чтобы вставка не сбивала индексы ещё не обработанных абзацев.
        for ($i = $count - 1; $i >= 0; $i--) {
            $image = $images[$i];
            $figure = '<figure>'
                . '<img src="' . $image['post'] . '" alt="' . $this->esc($this->imageAlt($image['post'])) . '">'
                . '<figcaption>' . $this->esc($faker->randomElement(self::IMAGE_CAPTIONS)) . '</figcaption>'
                . '</figure>';

            array_splice($blocks, $targets[$i] + 1, 0, [$figure]);
        }

        return $blocks;
    }

    /**
     * alt для картинки из имени файла — он хотя бы описывает кадр.
     */
    private function imageAlt(string $path): string
    {
        $name = pathinfo(basename($path), PATHINFO_FILENAME);
        $name = str_replace(['seed-', '_'], ['', ' '], $name);

        return 'Фото: ' . trim($name);
    }
}
