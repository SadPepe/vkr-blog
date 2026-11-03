<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PopularArticleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ImageController;


Route::get('/', [ArticleController::class, 'index'])->name('home');
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::get('/search', [ArticleController::class, 'search'])->name('articles.search');

Route::get('/authors/{user}', [AuthorController::class, 'show'])->name('authors.show');

Route::get('/categories/{category:slug}', [ArticleController::class, 'articlesByCategory'])->name('articlesByCategory');


Route::middleware('auth')->group(function () {

    Route::post('/articles/{article:slug}/like', [LikeController::class, 'toggle'])->name('articles.like.toggle');
    Route::get('/articles/{article:slug}/like/status', [LikeController::class, 'status'])->name('articles.like.status');

    Route::post('/articles/{article:slug}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/like', [CommentController::class, 'toggleLike'])->name('comments.like.toggle');


    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/edit', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/create', [ArticleController::class, 'create'])->name('create');
    Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::post('/upload-image', [ImageController::class, 'upload']);
    Route::get('/articles/{article:slug}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article:slug}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article:slug}', [ArticleController::class, 'destroy'])->name('articles.destroy');
});
Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])
    ->name('articles.show');



require __DIR__ . '/auth.php';
