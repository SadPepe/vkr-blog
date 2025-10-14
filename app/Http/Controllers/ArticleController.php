<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Traits\HandlesArticles;
use Illuminate\Support\Facades\Storage;




class ArticleController extends Controller
{
    use HandlesArticles;


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:200',
            'content' => 'required|string',
            'card_image' => 'nullable|image|max:2048', // 2MB max
            'category_slug' => 'nullable|exists:categories,slug'
        ]);

        // Генерация уникального slug
        $slug = $this->generateUniqueSlug($validated['title']);

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
            'content' => $validated['content'],
            'card_image_path' => $cardImagePath,
            'user_id' => auth()->id(),
            'category_slug' => $validated['category_slug']
        ]);

        return redirect()->route('articles.show', $article->slug);


    }

    public function destroy(Article $article)
    {   Gate::authorize('delete', $article);

        try {
            // Удаляем связанное изображение
            if ($article->card_image_path) {
                $imagePath = str_replace('/storage/', '', $article->card_image_path);
                Storage::delete('public/' . $imagePath);
            }

            // Удаляем статью
            $article->delete();
            return redirect()->route('profile');
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
            'content' => 'required|string',
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

        $content = $validated['content'];

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
            'category_slug' => $validated['category_slug']
        ]);

        return redirect()->route('profile');
    }

    public function show(Article $article)
    {
        return Inertia::render('Articles/Show', [
            'article' => $article->load('user')
        ]);
    }

    public function index()
    {
        return inertia('Home', [
            'articles' => Article::with('user')->latest()->paginate(12)
        ]);
    }

    public function search(Request $request)
    {

        $request->validate([
            'query' => 'nullable|string|max:255'
        ]);

        $search = $request->input('query');

        return inertia('SearchResults', [
            'articles' => Article::query()
                ->when($search, function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                })->with('user')
                ->latest()
                ->paginate(12),
            'searchQuery' => $search
        ]);


    }




    public function create()
    {
        return Inertia::render('Articles/Create');   // Vue component
    }

    public function articlesByCategory($categorySlug = null)
    {
        $articles = Article::all();
        $categories = Category::all();

        if ($categorySlug) {

            $articles = Article::where('category_slug', $categorySlug)->with("user")->latest()->paginate(10);
            $activeCategory = $categorySlug;

            return Inertia::render('Home', [
                'articles' => $articles,
                'categories' => $categories,
                'activeCategory' => $activeCategory
            ]);

        }




    }








}

