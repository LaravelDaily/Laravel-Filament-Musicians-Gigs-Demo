<?php

namespace App\Filament\Resources\Instruments;

use App\Filament\Resources\Instruments\Pages\ManageInstruments;
use App\Models\Instrument;
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

class InstrumentResource extends Resource
{
    protected static ?string $model = Instrument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

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
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Instrument $record) {
                        if ($record->users()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete instrument')
                                ->body('This instrument has musicians assigned to it.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, $records) {
                            $hasAssignedMusicians = $records->some(fn (Instrument $record) => $record->users()->exists());

                            if ($hasAssignedMusicians) {
                                Notification::make()
                                    ->danger()
                                    ->title('Cannot delete instruments')
                                    ->body('Some instruments have musicians assigned to them.')
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
            'index' => ManageInstruments::route('/'),
        ];
    }
}
