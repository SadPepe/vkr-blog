<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['reporter:id,name', 'reportable']))
            ->columns([
                TextColumn::make('reportable_type')
                    ->label('Объект')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'article' ? 'Пост' : 'Комментарий')
                    ->color(fn (?string $state): string => $state === 'article' ? 'info' : 'gray'),
                TextColumn::make('object')
                    ->label('Текст')
                    ->state(fn (Report $record): string => self::objectLabel($record))
                    ->limit(120)
                    ->wrap(),
                TextColumn::make('post')
                    ->label('Пост')
                    ->state(fn (Report $record): ?string => self::postLabel($record))
                    ->limit(40)
                    ->url(fn (Report $record): ?string => self::targetUrl($record))
                    ->openUrlInNewTab()
                    ->placeholder('не найден'),
                TextColumn::make('reason')
                    ->label('Причина')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Report::REASONS[$state] ?? (string) $state),
                TextColumn::make('comment')
                    ->label('Пояснение')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('reporter.name')
                    ->label('Автор жалобы')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Report::STATUSES[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        Report::STATUS_NEW => 'warning',
                        Report::STATUS_ACCEPTED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Получена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Report::STATUSES),
                SelectFilter::make('reason')
                    ->label('Причина')
                    ->options(Report::REASONS),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Открыть')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Report $record): ?string => self::targetUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Report $record): bool => self::targetUrl($record) !== null),
                Action::make('hideContent')
                    ->label('Скрыть контент')
                    ->icon('heroicon-o-eye-slash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Скрыть контент и закрыть жалобу')
                    ->visible(fn (Report $record): bool => $record->status === Report::STATUS_NEW
                        && $record->reportable !== null
                        && Gate::allows('update', $record))
                    ->action(function (Report $record): void {
                        $target = $record->reportable;

                        if ($target instanceof Article) {
                            $target->update(['status' => Article::STATUS_HIDDEN]);
                        } elseif ($target instanceof Comment) {
                            $target->update(['hidden_at' => now()]);
                            $target->article?->syncCommentsCount();
                        }

                        self::resolve($record, Report::STATUS_ACCEPTED);
                    }),
                Action::make('accept')
                    ->label('Принять')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Контент остаётся на сайте, жалоба закрывается.')
                    ->visible(fn (Report $record): bool => $record->status === Report::STATUS_NEW && Gate::allows('update', $record))
                    ->action(fn (Report $record) => self::resolve($record, Report::STATUS_ACCEPTED)),
                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->status === Report::STATUS_NEW && Gate::allows('update', $record))
                    ->action(fn (Report $record) => self::resolve($record, Report::STATUS_REJECTED)),
            ]);
    }

    private static function objectLabel(Report $record): string
    {
        $target = $record->reportable;

        if ($target instanceof Article) {
            return $target->title ?: 'Пост без заголовка';
        }

        if ($target instanceof Comment) {
            $prefix = $target->isHidden() ? '[скрыт] ' : '';

            return $prefix . Str::limit((string) $target->content, 120);
        }

        return 'Удалено';
    }

    /**
     * Пост, к которому относится жалоба: для комментария — его статья.
     */
    private static function postLabel(Report $record): ?string
    {
        $target = $record->reportable;

        if ($target instanceof Article) {
            return $target->title ?: $target->slug;
        }

        if ($target instanceof Comment) {
            return $target->article?->title ?: $target->article?->slug;
        }

        return null;
    }

    /**
     * Ссылка на материал; для комментария — с якорем на конкретный комментарий.
     */
    private static function targetUrl(Report $record): ?string
    {
        $target = $record->reportable;

        if ($target instanceof Article) {
            return route('articles.show', ['article' => $target->slug]);
        }

        if ($target instanceof Comment && $target->article) {
            return route('articles.show', ['article' => $target->article->slug]) . '#comment-' . $target->id;
        }

        return null;
    }

    private static function resolve(Report $record, string $status): void
    {
        $record->update([
            'status' => $status,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);
    }
}
