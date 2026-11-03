<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesArticles;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Storage;





class ArticleController extends Controller
{
    use HandlesArticles;


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:200',
            'content' => 'required|string|max:200000',
            'card_image' => 'nullable|image|max:2048', // 2MB max
            'category_slug' => 'nullable|exists:categories,slug'
        ]);

        // Генерация уникального slug
        $slug = $this->generateUniqueSlug($validated['title']);

        //Санитизация HTML от XSS (вырезаются script, on* , javascript: и т.п.)
        $content = HtmlSanitizer::sanitize($validated['content']);

        //Сохраняем картинку карточки в Storage и сохраняем путь
        $cardImagePath = null;
        if ($request->hasFile('card_image')) {
            $path = $request->file('card_image')->store('article-cards', 'public');
            $cardImagePath = '/storage/' . $path;
        }

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'content' => $content,
            'card_image_path' => $cardImagePath,
            'user_id' => auth()->id(),
            'category_slug' => $validated['category_slug'] ?? null
        ]);

        return redirect()
            ->route('articles.show', $article->slug)
            ->with('success', 'Статья опубликована');
    }

    public function destroy(Article $article)
    {
        Gate::authorize('delete', $article);

        try {
            // Удаляем связанное изображение карточки
            if ($article->card_image_path) {
                $imagePath = str_replace('/storage/', '', $article->card_image_path);
                Storage::delete('public/' . $imagePath);
            }

            // Удаляем изображения, встроенные в контент
            $this->deleteContentImages($article->content);

            // Удаляем статью
            $article->delete();

            return redirect()
                ->route('profile')
                ->with('success', 'Статья удалена');
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Article $article)
    {
        Gate::authorize('update', $article);

        return Inertia::render('Articles/Edit', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'card_image_url' => $article->card_image_path, // Уже содержит '/storage/'
                'slug' => $article->slug,
                'category_slug' => $article->category_slug,
            ]
        ]);
    }


    public function update(Request $request, Article $article)
    {
        Gate::authorize('update', $article);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:200',
            'content' => 'required|string|max:200000',
            'card_image' => 'nullable|image|max:2048',
            'remove_card_image' => 'nullable|boolean',
            'category_slug' => 'nullable|exists:categories,slug'
        ]);

        // Обработка изображения карточки
        $cardImagePath = $article->card_image_path;

        if ($request->has('remove_card_image') && $request->remove_card_image) {
            // Удаляем текущее изображение если есть
            if ($cardImagePath) {
                $this->deleteImage($cardImagePath);
                $cardImagePath = null;
            }
        } elseif ($request->hasFile('card_image')) {
            if ($cardImagePath) {
                $this->deleteImage($cardImagePath);
            }
            // Сохраняем новое изображение
            $path = $request->file('card_image')->store('article-cards', 'public');
            $cardImagePath = '/storage/' . $path;
        }

        $oldContent = $article->content;

        // Санитизация HTML от XSS
        $content = HtmlSanitizer::sanitize($validated['content']);

        // Удаляем изображения, которые убрали из контента редактором
        $this->deleteRemovedContentImages($oldContent, $content);

        // Обновление slug только если изменился заголовок
        $slug = $article->slug;
        if ($article->title !== $validated['title']) {
            $slug = $this->generateUniqueSlug($validated['title']);
        }

        $article->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'content' => $content,
            'card_image_path' => $cardImagePath,
            'category_slug' => $validated['category_slug'] ?? null
        ]);

        return redirect()
            ->route('profile')
            ->with('success', 'Статья обновлена');
    }

    public function show(Article $article)
    {
        // Обновляем метод увеличения просмотров
        $article->incrementViews();

        $article->load([
            'user:id,name,about,avatar',
            'comments.user:id,name,avatar',
            'comments.replies.user:id,name,avatar',
            'comments.likes',
            'comments.replies.likes',
        ]);

        $article->is_liked = auth()->check() ? $article->getIsLikedAttribute() : false;

        // Помечаем комментарии: лайкнул ли их текущий пользователь
        $article->comments->each(function ($comment) {
            $comment->markLikedByCurrentUser();
            $comment->replies->each(fn ($reply) => $reply->markLikedByCurrentUser());
        });

        // Похожие статьи из той же категории
        $relatedArticles = $article->category_slug
            ? Article::where('category_slug', $article->category_slug)
                ->whereKeyNot($article->id)
                ->latest()
                ->limit(4)
                ->select(Article::CARD_COLUMNS)
                ->get()
            : collect();

        $popularArticleController = new \App\Http\Controllers\PopularArticleController();

        return Inertia::render('Articles/Show', [
            'article' => $article,
            'popularArticles' => $popularArticleController->getPopularArticles(4),
            'relatedArticles' => $relatedArticles,
        ]);
    }

    public function index(Request $request)
    {
        $sort = $this->resolveSort($request);

        $articles = $this->applySort(
            Article::with(['user:id,name,avatar'])->select(Article::CARD_COLUMNS),
            $sort
        )
            ->paginate(12)
            ->withQueryString();

        // Используем PopularArticleController для получения популярных статей
        $popularArticleController = new \App\Http\Controllers\PopularArticleController();
        $popularArticles = $popularArticleController->getPopularArticles(5, 'week');

        $categories = Category::withCount('articles')->orderBy('name')->get();

        return inertia('Home', [
            'articles' => $articles,
            'popularArticles' => $popularArticles,
            'categories' => $categories,
            'sort' => $sort
        ]);
    }

    public function search(Request $request)
    {

        $request->validate([
            'query' => 'nullable|string|max:255'
        ]);

        $search = trim((string) $request->input('query'));

        // PostgreSQL не поддерживает LIKE без учёта регистра для кириллицы,
        // поэтому используем ILIKE на pgsql и LIKE на остальных драйверах
        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $pattern = "%{$search}%";

        return inertia('SearchResults', [
            'articles' => Article::query()
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
            'searchQuery' => $search ?: null
        ]);
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

            $articles = $this->applySort(
                Article::where('category_slug', $categorySlug)
                    ->with(['user:id,name,avatar'])
                    ->select(Article::CARD_COLUMNS),
                $sort
            )
                ->paginate(10)
                ->withQueryString();

            // Для категорий тоже используем популярные статьи за неделю
            $popularArticles = Article::popularForPeriod(5, 'week', $categorySlug);

            return Inertia::render('Home', [
                'articles' => $articles,
                'categories' => $categories,
                'popularArticles' => $popularArticles,
                'activeCategory' => $categorySlug,
                'sort' => $sort
            ]);
        }

        return $this->index();
    }
}
