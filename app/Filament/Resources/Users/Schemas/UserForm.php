<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Логин')
                    ->required()
                    ->maxLength(32)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('role')
                    ->label('Роль')
                    ->options(User::ROLES)
                    ->required()
                    ->native(false)
                    ->helperText('Модератор скрывает контент, администратор удаляет его и управляет пользователями.'),
                Textarea::make('about')
                    ->label('О себе')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }
}
