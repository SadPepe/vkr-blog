<?php

use App\Models\ArticleView;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ретенция детальных просмотров: IP и User-Agent храним 12 месяцев,
// как обещано в политике обработки персональных данных.
Artisan::command('views:prune {--days=365}', function () {
    $days = max(1, (int) $this->option('days'));

    $deleted = ArticleView::where('created_at', '<', now()->subDays($days))->delete();

    $this->info("Удалено {$deleted} записей article_views старше {$days} дней.");
})->purpose('Удаляет старые IP/User-Agent из article_views');

Schedule::command('views:prune')->dailyAt('03:30')->withoutOverlapping();
