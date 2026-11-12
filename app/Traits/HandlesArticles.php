<?php 

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Article;

trait HandlesArticles
{
    protected function generateUniqueSlug($title)
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
     * Возвращает допустимый ключ сортировки (по умолчанию - новые первыми).
     */
    protected function resolveSort(Request $request): string
    {
        $allowed = ['latest', 'oldest', 'popular', 'likes', 'comments'];
        $sort = $request->input('sort');

        return in_array($sort, $allowed, true) ? $sort : 'latest';
    }

    /**
     * Применяет сортировку к запросу статей.
     */
    protected function applySort($query, string $sort)
    {
        return match ($sort) {
            'oldest' => $query->orderBy('created_at'),
            'popular' => $query->orderByDesc('views_count'),
            'likes' => $query->orderByDesc('likes_count'),
            'comments' => $query->orderByDesc('comments_count'),
            default => $query->latest(),
        };
    }

    protected function deleteImage($path)
    {
        if (str_starts_with($path, '/storage/')) {
            $relativePath = str_replace('/storage/', '', $path);
            Storage::disk('public')->delete($relativePath);
        }
    }

    /**
     * Достаёт из HTML-контента пути встроенных изображений
     * (/storage/images/*), сохранённых в публичном диске.
     */
    protected function extractContentImagePaths($content): array
    {
        $paths = [];
        if (!$content) {
            return $paths;
        }

        if (preg_match_all('#/storage/images/([^"\']+)#i', $content, $matches)) {
            foreach ($matches[1] as $file) {
                $paths[] = trim($file);
            }
        }

        return array_values(array_unique($paths));
    }

    protected function deleteContentImages($content): void
    {
        foreach ($this->extractContentImagePaths($content) as $file) {
            Storage::disk('public')->delete('images/' . $file);
        }
    }

    /**
     * Удаляет файлы статьи: обложку карточки и картинки из контента.
     * Используется и при удалении статьи, и при удалении аккаунта.
     */
    protected function deleteArticleFiles(Article $article): void
    {
        if ($article->card_image_path) {
            $relative = ltrim(str_replace('/storage/', '', $article->card_image_path), '/');
            Storage::disk('public')->delete($relative);
        }

        $this->deleteContentImages($article->content);
    }

    /**
     * Удаляет изображения, которые были в старом контенте, но исчезли
     * из нового (картинку удалили из редактора).
     */
    protected function deleteRemovedContentImages($oldContent, $newContent): void
    {
        $removed = array_diff(
            $this->extractContentImagePaths($oldContent),
            $this->extractContentImagePaths($newContent)
        );

        foreach ($removed as $file) {
            Storage::disk('public')->delete('images/' . $file);
        }
    }

    /**
     * Заголовок необязателен: пустая строка превращается в null.
     */
    protected function normalizeTitle(?string $title): ?string
    {
        $title = trim(preg_replace('/\s+/u', ' ', (string) $title));

        return $title === '' ? null : $title;
    }

    /**
     * Короткий анонс для ленты: текст без HTML, до 200 символов.
     */
    protected function makeExcerpt(string $html, int $limit = 200): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));

        return Str::limit($text, $limit, '');
    }

    /**
     * Пост считается пустым, если в нём нет ни текста, ни изображения, ни видео.
     */
    protected function contentIsEmpty(string $html): bool
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
        $hasVideo = preg_match('/<div\b[^>]*\bclass=["\'][^"\']*\bvideo-embed\b/i', $html) === 1;

        return $text === '' && $this->firstContentImage($html) === null && ! $hasVideo;
    }

    /**
     * Первая картинка из текста поста — она же превью карточки в ленте.
     */
    protected function firstContentImage(string $html): ?string
    {
        if (preg_match('#/storage/images/[^"\'\s>]+#i', $html, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Slug: из заголовка, иначе из текста, иначе случайный.
     */
    protected function generatePostSlug(?string $title, string $excerpt): string
    {
        $base = trim((string) ($title ?: $excerpt));

        if ($base === '') {
            return 'post-' . Str::lower(Str::random(8));
        }

        return $this->generateUniqueSlug(Str::limit($base, 60, ''));
    }

    /**
     * Старая обложка остаётся, только если файл ещё существует.
     */
    protected function existingImagePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $relative = ltrim(str_replace('/storage/', '', $path), '/');

        return Storage::disk('public')->exists($relative) ? $path : null;
    }

}
