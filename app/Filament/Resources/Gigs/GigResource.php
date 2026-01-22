<?php

namespace App\Filament\Resources\Gigs;

use App\Filament\Resources\Gigs\Pages\CreateGig;
use App\Filament\Resources\Gigs\Pages\EditGig;
use App\Filament\Resources\Gigs\Pages\ListGigs;
use App\Filament\Resources\Gigs\Pages\ViewGig;
use App\Filament\Resources\Gigs\Schemas\GigForm;
use App\Filament\Resources\Gigs\Tables\GigsTable;
use App\Models\Gig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GigResource extends Resource
{
    protected static ?string $model = Gig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $modelLabel = 'Gig';

    protected static ?string $pluralModelLabel = 'Gigs';

    protected static ?string $slug = 'gigs';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return GigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GigsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGigs::route('/'),
            'create' => CreateGig::route('/create'),
            'view' => ViewGig::route('/{record}'),
            'edit' => EditGig::route('/{record}/edit'),
        ];
    }
}
