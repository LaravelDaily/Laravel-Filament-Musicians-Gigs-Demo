<?php

namespace App\Filament\Resources\Regions;

use App\Filament\Resources\Regions\Pages\ManageRegions;
use App\Models\Region;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Musicians'),
                TextColumn::make('gigs_count')
                    ->counts('gigs')
                    ->label('Gigs'),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Region $record) {
                        if ($record->users()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete region')
                                ->body('This region has musicians assigned to it.')
                                ->send();

                            $action->cancel();
                        }

                        if ($record->gigs()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete region')
                                ->body('This region has gigs assigned to it.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, $records) {
                            $hasAssignedUsersOrGigs = $records->some(
                                fn (Region $record) => $record->users()->exists() || $record->gigs()->exists()
                            );

                            if ($hasAssignedUsersOrGigs) {
                                Notification::make()
                                    ->danger()
                                    ->title('Cannot delete regions')
                                    ->body('Some regions have musicians or gigs assigned to them.')
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRegions::route('/'),
        ];
    }
}
