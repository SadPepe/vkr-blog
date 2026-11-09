<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('body')
                    ->label('Текст объявления')
                    ->required()
                    ->maxLength(200)
                    ->helperText('Одна строка — она бежит по шапке сайта. Без HTML.')
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->label('Ссылка')
                    ->url()
                    ->maxLength(500)
                    ->helperText('Необязательно. Если указана, текст станет ссылкой (например, на пост).')
                    ->columnSpanFull(),
                DateTimePicker::make('starts_at')
                    ->label('Показывать с')
                    ->seconds(false)
                    ->helperText('Пусто — сразу. Время в часовом поясе сайта (Москва).'),
                DateTimePicker::make('ends_at')
                    ->label('Показывать до')
                    ->seconds(false)
                    ->after('starts_at')
                    ->helperText('Пусто — бессрочно. Если дата уже прошла, объявление не показывается.'),
                TextInput::make('sort_order')
                    ->label('Приоритет')
                    ->numeric()
                    ->default(0)
                    ->helperText('Больше — выше в строке.'),
                Toggle::make('is_active')
                    ->label('Включено')
                    ->default(true),
            ]);
    }
}
