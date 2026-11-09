<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Report;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ModerationStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Пользователи', User::count())
                ->description('Всего зарегистрировано')
                ->color('gray'),
            Stat::make('Опубликовано', Article::visible()->count())
                ->description('Постов видно на сайте')
                ->color('success'),
            Stat::make('Скрыто', Article::where('status', Article::STATUS_HIDDEN)->count() + Comment::whereNotNull('hidden_at')->count())
                ->description('Постов и комментариев')
                ->color('warning'),
            Stat::make('Жалобы', Report::pending()->count())
                ->description('Ждут рассмотрения')
                ->color(Report::pending()->count() > 0 ? 'danger' : 'success'),
        ];
    }
}
