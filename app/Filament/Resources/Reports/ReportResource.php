<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\Pages\ListReports;
use App\Models\Report;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|\UnitEnum|null $navigationGroup = 'Модерация';

    protected static ?string $navigationLabel = 'Жалобы';

    protected static ?string $modelLabel = 'жалоба';

    protected static ?string $pluralModelLabel = 'Жалобы';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Reports\Tables\ReportsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
        ];
    }
}
