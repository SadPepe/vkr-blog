<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Support\AuthorPreview;
use App\Support\ContentNormalizer;
use App\Support\HtmlSanitizer;
use App\Support\MentionRenderer;
use App\Support\PostPreview;
use App\Traits\HandlesArticles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ArticleController extends Controller
{
    use HandlesArticles;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:200000',
            'category_slug' => 'nullable|exists:categories,slug',
        ]);

        // Санитизация HTML от XSS (вырезаются script, on* , javascript: и т.п.)
        // и разведение абзацев: вставка из буфера приходит одним <p> с <br>
        $content = ContentNormalizer::paragraphs(HtmlSanitizer::sanitize($validated['content']));

        if ($this->contentIsEmpty($content)) {
            throw ValidationException::withMessages([
                'content' => 'Добавьте текст, изображение или видео — пустой пост опубликовать нельзя.',
            ]);
        }

        $title = $this->normalizeTitle($validated['title'] ?? null);
        $excerpt = $this->makeExcerpt($content);

        $article = Article::create([
            'title' => $title,
            'slug' => $this->generatePostSlug($title, $excerpt),
            'excerpt' => $excerpt,
            'content' => $content,
            // Отдельной обложки больше нет: превью — первая картинка из текста
            'card_image_path' => $this->firstContentImage($content),
            'user_id' => auth()->id(),
            'category_slug' => $validated['category_slug'] ?? null,
        ]);

        return redirect()
            ->route('articles.show', $article->slug)
            ->with('success', 'Пост опубликован');
    }

    public function destroy(Article $article)
    {
        Gate::authorize('delete', $article);

        try {
            $this->deleteArticleFiles($article);

            $article->delete();

            return redirect()
                ->route('profile')
                ->with('success', 'Пост удалён');
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Закрепить пост в своём блоге или снять закрепление.
     * Закреплённый пост один: новый пин снимает предыдущий.
     */
    public function pin(Article $article)
    {
        Gate::authorize('update', $article);

        if (! $article->canBePinned()) {
            return back()->with('error', 'Скрытый пост закрепить нельзя — сначала опубликуйте его.');
        }

        $pinned = ! $article->is_pinned;

        DB::transaction(function () use ($article, $pinned) {
            Article::where('user_id', $article->user_id)
                ->where('is_pinned', true)
                ->whereKeyNot($article->id)
                ->update(['is_pinned' => false, 'pinned_at' => null]);

            $article->update([
                'is_pinned' => $pinned,
                'pinned_at' => $pinned ? now() : null,
            ]);
        });

        return back()->with('success', $pinned ? 'Пост закреплён' : 'Пост откреплён');
    }

    /**
     * Скрыть свой пост от читателей или вернуть его в ленты.
     * Скрытый пост остаётся в профиле автора и доступен только ему и модераторам.
     */
    public function visibility(Article $article)
    {
        Gate::authorize('update', $article);

        $hide = $article->isVisible();

        $article->update([
            'status' => $hide ? Article::STATUS_HIDDEN : Article::STATUS_PUBLISHED,
            'published_at' => $hide ? $article->published_at : ($article->published_at ?? now()),
            // Скрытый пост не остаётся закреплённым: закрепление обещает его читателям
            'is_pinned' => $hide ? false : $article->is_pinned,
            'pinned_at' => $hide ? null : $article->pinned_at,
        ]);

        return back()->with('success', $hide ? 'Пост скрыт — он виден только вам' : 'Пост снова опубликован');
    }

    public function edit(Article $article)
    {
        Gate::authorize('update', $article);

        return Inertia::render('Articles/Edit', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'content' => ContentNormalizer::paragraphs((string) $article->content),
                'slug' => $article->slug,
                'category_slug' => $article->category_slug,
            ],
        ]);
    }

    public function update(Request $request, Article $article)
    {
        Gate::authorize('update', $article);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:200000',
            'category_slug' => 'nullable|exists:categories,slug',
        ]);

        $oldContent = $article->content;

        // Санитизация HTML от XSS и разведение абзацев (см. store)
        $content = ContentNormalizer::paragraphs(HtmlSanitizer::sanitize($validated['content']));

        if ($this->contentIsEmpty($content)) {
            throw ValidationException::withMessages([
                'content' => 'Добавьте текст, изображение или видео — пустой пост сохранить нельзя.',
            ]);
        }

        // Удаляем изображения, которые убрали из контента редактором
        $this->deleteRemovedContentImages($oldContent, $content);

        $title = $this->normalizeTitle($validated['title'] ?? null);
        $excerpt = $this->makeExcerpt($content);

        // Slug меняется вместе с заголовком (или текстом, из которого он собран)
        $slug = $article->slug;
        if ($article->title !== $title) {
            $slug = $this->generatePostSlug($title, $excerpt);
        }

        // Превью — первая картинка из текста; старую обложку сохраняем,
        // только если её файл ещё существует
        $cardImagePath = $this->firstContentImage($content)
            ?? $this->existingImagePath($article->card_image_path);

        $article->update([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'card_image_path' => $cardImagePath,
            'category_slug' => $validated['category_slug'] ?? null,
        ]);

        return redirect()
            ->route('profile')
            ->with('success', 'Пост обновлён');
    }

    public function show(Article $article)
    {
        // Скрытый пост доступен автору и модераторам, остальным — 404
        $this->ensureCanView($article);

        // Просмотры считаем только у опубликованного: автор в своём скрытом
        // посте не должен накручивать счётчик
        if ($article->isVisible()) {
            $article->incrementViews();
        }

        $article->load([
            'user:id,name,about,avatar',
            'comments.user:id,name,avatar',
            'comments.replies.user:id,name,avatar',
            'comments.likes',
            'comments.replies.likes',
        ]);

        $article->is_liked = auth()->check() ? $article->getIsLikedAttribute() : false;

        // Старые посты могли сохраниться одним <p> с <br>: на показе разводим по абзацам
        $article->content = MentionRenderer::render(ContentNormalizer::paragraphs((string) $article->content));

        // Помечаем комментарии: лайкнул ли их текущий пользователь
        $article->comments->each(function ($comment) {
            $comment->markLikedByCurrentUser();
            $comment->replies->each(fn ($reply) => $reply->markLikedByCurrentUser());
        });

        // Похожие статьи из той же категории
        $relatedArticles = $article->category_slug
            ? Article::visible()->where('category_slug', $article->category_slug)
                ->whereKeyNot($article->id)
                ->with('user:id,name,avatar')
                ->latest()
                ->limit(4)
                ->select(Article::CARD_COLUMNS)
                ->get()
                ->each(function (Article $related) {
                    // Рейл «Ещё в разделе» показывает автора и подпись так же,
                    // как остальные списки: без этого строка выглядела пустой.
                    $related->label = Article::labelFor($related->title, $related->excerpt);
                    $related->is_photo_post = $related->isPhotoPost();

                    return $related;
                })
            : collect();

        $popularArticleController = new \App\Http\Controllers\PopularArticleController;

        return Inertia::render('Articles/Show', [
            'article' => $article,
            'popularArticles' => $popularArticleController->getPopularArticles(4),
            'relatedArticles' => $relatedArticles,
        ]);
    }

    /**
     * Развёрнутая часть поста для ленты: тот же авторский порядок блоков,
     * но с большими лимитами. Полный content в ленты по-прежнему не отдаётся.
     */
    public function excerpt(Article $article)
    {
        // Черновик или скрытый пост не должен утекать через ленивую подгрузку
        $this->ensureCanView($article);

        $preview = PostPreview::excerpt((string) $article->content);
        $preview['html'] = MentionRenderer::render($preview['html']);

        return response()->json($preview);
    }

    public function index(Request $request)
    {
        $sort = $this->resolveSort($request);

        $articles = PostPreview::attachPaginator(
            $this->applySort(
                Article::visible()
                    ->with('user:id,name,avatar')
                    ->select(array_merge(Article::CARD_COLUMNS, ['content'])),
                $sort
            )->paginate(6)
        )->withQueryString();

        // Используем PopularArticleController для получения популярных статей
        $popularArticleController = new \App\Http\Controllers\PopularArticleController;
        $popularArticles = $popularArticleController->getPopularArticles(5, 'week');

        $categories = Category::withCount('articles')->orderBy('name')->get();

        return inertia('Home', [
            'articles' => $articles,
            'popularArticles' => $popularArticles,
            'discussedArticles' => $this->discussedArticles(),
            'activeAuthors' => $this->activeAuthors(),
            'categories' => $categories,
            'sort' => $sort,
        ]);
    }

    public function search(Request $request)
    {

        $request->validate([
            'query' => 'nullable|string|max:255',
        ]);

        $search = trim((string) $request->input('query'));

        // PostgreSQL не поддерживает LIKE без учёта регистра для кириллицы,
        // поэтому используем ILIKE на pgsql и LIKE на остальных драйверах
        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $pattern = "%{$search}%";

        return inertia('SearchResults', [
            'articles' => Article::query()
                ->visible()
                ->with('user:id,name,avatar')
                ->when($search, function ($query) use ($pattern, $likeOp) {
                    $query->where(function ($q) use ($pattern, $likeOp) {
                        $q->where('title', $likeOp, $pattern)
                            ->orWhere('excerpt', $likeOp, $pattern)
                            ->orWhere('content', $likeOp, $pattern);
                    });
                })
                ->select(Article::CARD_COLUMNS)
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'searchQuery' => $search ?: null,
        ]);
    }

    /**
     * Посты, которые сейчас активнее всего обсуждают.
     */
    private function discussedArticles(?string $categorySlug = null, int $limit = 4)
    {
        return Article::visible()
            ->when($categorySlug, fn ($query) => $query->where('category_slug', $categorySlug))
            ->where('comments_count', '>', 0)
            ->with('user:id,name,avatar')
            ->orderByDesc('comments_count')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(Article::CARD_COLUMNS)
            ->each(function (Article $article) {
                $article->label = Article::labelFor($article->title, $article->excerpt);
                $article->is_photo_post = $article->isPhotoPost();

                return $article;
            });
    }

    /**
     * Активные авторы: те, кто публиковался недавно, а не вечный топ по просмотрам.
     */
    private function activeAuthors(int $limit = 4)
    {
        return AuthorPreview::query()
            ->when(auth()->check(), fn ($query) => $query->whereKeyNot(auth()->id()))
            ->orderByDesc('last_published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn ($author) => AuthorPreview::map($author));
    }

    public function create()
    {
        return Inertia::render('Articles/Create');   // Vue component
    }

    public function articlesByCategory($category = null)
    {
        $categories = Category::withCount('articles')->orderBy('name')->get();

        // При маршрутной привязке {category:slug} сюда приходит модель Category;
        // извлекаем из неё slug, чтобы корректно фильтровать статьи
        $categorySlug = $category instanceof Category ? $category->slug : $category;

        if ($categorySlug) {
            $sort = $this->resolveSort(request());

            $articles = PostPreview::attachPaginator(
                $this->applySort(
                    Article::visible()
                        ->where('category_slug', $categorySlug)
                        ->with('user:id,name,avatar')
                        ->select(array_merge(Article::CARD_COLUMNS, ['content'])),
                    $sort
                )->paginate(6)
            )->withQueryString();

            // Для категорий тоже используем популярные статьи за неделю
            $popularArticles = Article::popularForPeriod(5, 'week', $categorySlug);

            return Inertia::render('Home', [
                'articles' => $articles,
                'categories' => $categories,
                'popularArticles' => $popularArticles,
                'discussedArticles' => $this->discussedArticles($categorySlug),
                'activeAuthors' => $this->activeAuthors(),
                'activeCategory' => $categorySlug,
                'sort' => $sort,
            ]);
        }

        return $this->index();
    }

    /**
     * Опубликованное видят все; черновик и скрытое — только автор и модераторы.
     */
    private function ensureCanView(Article $article): void
    {
        if ($article->isVisible()) {
            return;
        }

        $user = auth()->user();

        abort_if(
            ! $user || ($user->id !== $article->user_id && ! $user->canModerate()),
            404
        );
    }
}
