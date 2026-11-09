<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label('Логин')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('role')
                    ->label('Роль')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => User::ROLES[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_MODERATOR => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('articles_count')
                    ->label('Посты')
                    ->counts('articles')
                    ->sortable(),
                TextColumn::make('comments_count')
                    ->label('Комментарии')
                    ->counts('comments'),
                IconColumn::make('blocked_at')
                    ->label('Блок')
                    ->state(fn (User $record): bool => $record->isBlocked())
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Роль')
                    ->options(User::ROLES),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggleBlock')
                    ->label(fn (User $record): string => $record->isBlocked() ? 'Разблокировать' : 'Заблокировать')
                    ->icon('heroicon-o-no-symbol')
                    ->color(fn (User $record): string => $record->isBlocked() ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => $record->isBlocked() ? 'Разблокировать пользователя' : 'Заблокировать пользователя')
                    ->modalDescription('Заблокированный не сможет войти на сайт и в панель управления.')
                    ->action(fn (User $record) => $record->forceFill([
                        'blocked_at' => $record->isBlocked() ? null : now(),
                    ])->save())
                    ->visible(fn (User $record): bool => auth()->user()?->isAdmin() && auth()->id() !== $record->id),
            ]);
    }
}
