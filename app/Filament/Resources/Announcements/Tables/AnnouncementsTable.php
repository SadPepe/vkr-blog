<?php

namespace App\Filament\Resources\Announcements\Tables;

use App\Models\Announcement;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'desc')
            ->columns([
                TextColumn::make('body')
                    ->label('Текст')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('url')
                    ->label('Ссылка')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('state')
                    ->label('Сейчас')
                    ->badge()
                    ->state(fn (Announcement $record): string => match (true) {
                        ! $record->is_active => 'Выключено',
                        $record->starts_at?->isFuture() => 'Запланировано',
                        $record->ends_at?->isPast() => 'Истекло',
                        default => 'Идёт',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Идёт' => 'success',
                        'Запланировано' => 'info',
                        'Истекло' => 'gray',
                        default => 'warning',
                    }),
                IconColumn::make('is_active')
                    ->label('Вкл.')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('starts_at')
                    ->label('С')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('сразу'),
                TextColumn::make('ends_at')
                    ->label('До')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('бессрочно'),
                TextColumn::make('sort_order')
                    ->label('Приоритет')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Включено'),
            ])
            ->recordActions([
                Action::make('toggle')
                    ->label(fn (Announcement $record): string => $record->is_active ? 'Выключить' : 'Включить')
                    ->icon('heroicon-o-power')
                    ->color(fn (Announcement $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Announcement $record) => $record->update(['is_active' => ! $record->is_active])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
