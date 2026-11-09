<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Слаг в URL')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->helperText('Латиница, цифры и дефис: /categories/<slug>. После создания не меняется — на него ссылаются все посты.')
                    ->disabledOn('edit')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
            ]);
    }

    public static function slugFromName(string $name): string
    {
        return Str::slug($name);
    }
}
