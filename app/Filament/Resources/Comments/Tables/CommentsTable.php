<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('content')
                    ->label('Комментарий')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Автор')
                    ->sortable(),
                TextColumn::make('article.title')
                    ->label('Пост')
                    ->placeholder('Без заголовка')
                    ->limit(30)
                    ->searchable(),
                IconColumn::make('hidden_at')
                    ->label('Скрыт')
                    ->state(fn (Comment $record): bool => $record->isHidden())
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('hidden')
                    ->label('Только скрытые')
                    ->toggle()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['isActive'] ?? false,
                        fn (Builder $q): Builder => $q->whereNotNull('hidden_at'),
                    )),
            ])
            ->recordActions([
                Action::make('hide')
                    ->label('Скрыть')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Скрыть комментарий')
                    ->modalDescription('Комментарий перестанет показываться на сайте, счётчик поста уменьшится.')
                    ->visible(fn (Comment $record): bool => ! $record->isHidden() && Gate::allows('hide', $record))
                    ->action(function (Comment $record): void {
                        $record->update(['hidden_at' => now()]);
                        $record->article?->syncCommentsCount();
                    }),
                Action::make('restore')
                    ->label('Вернуть')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Comment $record): bool => $record->isHidden() && Gate::allows('hide', $record))
                    ->action(function (Comment $record): void {
                        $record->update(['hidden_at' => null]);
                        $record->article?->syncCommentsCount();
                    }),
                DeleteAction::make()
                    ->label('Удалить')
                    ->after(function (Comment $record): void {
                        $record->article?->syncCommentsCount();
                    }),
            ]);
    }
}
