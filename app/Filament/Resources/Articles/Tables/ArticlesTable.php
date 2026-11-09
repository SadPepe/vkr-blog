<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            // Полный HTML поста (до 200 КБ) в таблицу не тянем.
            ->modifyQueryUsing(fn ($query) => $query->select([
                'id', 'title', 'slug', 'status', 'category_slug', 'user_id',
                'views_count', 'comments_count', 'created_at',
            ])->with('user:id,name'))
            ->columns([
                TextColumn::make('title')
                    ->label('Пост')
                    ->placeholder('Без заголовка')
                    ->limit(50)
                    ->searchable(['title', 'slug']),
                TextColumn::make('user.name')
                    ->label('Автор')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Article::STATUSES[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        Article::STATUS_HIDDEN => 'danger',
                        Article::STATUS_DRAFT => 'gray',
                        default => 'success',
                    }),
                TextColumn::make('category_slug')
                    ->label('Категория')
                    ->toggleable(),
                TextColumn::make('views_count')
                    ->label('Просмотры')
                    ->sortable(),
                TextColumn::make('comments_count')
                    ->label('Комментарии')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Article::STATUSES),
                SelectFilter::make('user')
                    ->label('Автор')
                    ->relationship('user', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Открыть')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Article $record): string => route('articles.show', ['article' => $record->slug]))
                    ->openUrlInNewTab()
                    ->visible(fn (Article $record): bool => $record->isVisible()),
                Action::make('hide')
                    ->label('Скрыть')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Скрыть пост')
                    ->modalDescription('Пост исчезнет из лент и поиска. Автор увидит его в профиле.')
                    ->visible(fn (Article $record): bool => $record->isVisible() && Gate::allows('hide', $record))
                    ->action(fn (Article $record) => $record->update(['status' => Article::STATUS_HIDDEN])),
                Action::make('restore')
                    ->label('Вернуть')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Article $record): bool => ! $record->isVisible() && Gate::allows('hide', $record))
                    ->action(fn (Article $record) => $record->update([
                        'status' => Article::STATUS_PUBLISHED,
                        'published_at' => $record->published_at ?? now(),
                    ])),
                DeleteAction::make()
                    ->label('Удалить'),
            ]);
    }
}
