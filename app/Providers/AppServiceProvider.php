<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Report;
use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CommentPolicy;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);

        // Один гейт на панель управления: модератор или админ.
        // Полиморфные жалобы хранят короткие имена типов, а не FQCN.
        Relation::morphMap([
            'article' => Article::class,
            'comment' => Comment::class,
        ]);

        Gate::define('moderate', fn (User $user) => $user->canModerate());
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        Vite::prefetch(concurrency: 3);
    }
}
