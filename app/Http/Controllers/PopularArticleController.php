<?php
// app/Http/Controllers/PopularArticleController.php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PopularArticleController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'week'); // day, week, month, all
        
        $articles = Article::query()
            ->with(['user'])
            ->when($period !== 'all', function ($query) use ($period) {
                $dateField = match($period) {
                    'day' => now()->subDay(),
                    'week' => now()->subWeek(),
                    'month' => now()->subMonth(),
                    default => now()->subWeek()
                };
                
                $query->where('created_at', '>=', $dateField);
            })
            ->orderBy('views_count', 'desc')
            ->orderBy('likes_count', 'desc')
            ->orderBy('comments_count', 'desc')
            ->paginate(20);

        return Inertia::render('Popular/Index', [
            'articles' => $articles,
            'period' => $period,
            'filters' => $request->only(['period'])
        ]);
    }

    public function getPopularArticles($limit = 5, $period = 'week')
    {
        return Article::popularForPeriod((int) $limit, $period);
    }
}