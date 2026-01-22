<?php

namespace App\Filament\Resources\Musicians;

use App\Enums\UserRole;
use App\Filament\Resources\Musicians\Pages\CreateMusician;
use App\Filament\Resources\Musicians\Pages\EditMusician;
use App\Filament\Resources\Musicians\Pages\ListMusicians;
use App\Filament\Resources\Musicians\Schemas\MusicianForm;
use App\Filament\Resources\Musicians\Tables\MusiciansTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MusicianResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'Musician';

    protected static ?string $pluralModelLabel = 'Musicians';

    protected static ?string $slug = 'musicians';

    public static function form(Schema $schema): Schema
    {
        return MusicianForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MusiciansTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', UserRole::Musician);
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
            'index' => ListMusicians::route('/'),
            'create' => CreateMusician::route('/create'),
            'edit' => EditMusician::route('/{record}/edit'),
        ];
    }
}
