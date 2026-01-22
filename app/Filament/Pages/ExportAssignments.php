<?php

namespace App\Filament\Pages;

use App\Filament\Exports\GigAssignmentExporter;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ExportAssignments extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Export Assignments';

    protected static ?string $title = 'Export Assignments';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.export-assignments';

    #[Url]
    public ?string $date_from = null;

    #[Url]
    public ?string $date_until = null;

    #[Url]
    public ?string $musician_id = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Grid::make(3)
                        ->schema([
                            DatePicker::make('date_from')
                                ->label('From Date')
                                ->live(),
                            DatePicker::make('date_until')
                                ->label('Until Date')
                                ->live(),
                            Select::make('musician_id')
                                ->label('Musician')
                                ->options(fn () => User::query()
                                    ->whereHas('assignments')
                                    ->orderBy('name')
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->live(),
                        ]),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(GigAssignmentExporter::class)
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query
                        ->with(['gig', 'user', 'instrument'])
                        ->when($this->date_from, fn (Builder $q) => $q->whereHas('gig', fn (Builder $gq) => $gq->whereDate('date', '>=', $this->date_from)))
                        ->when($this->date_until, fn (Builder $q) => $q->whereHas('gig', fn (Builder $gq) => $gq->whereDate('date', '<=', $this->date_until)))
                        ->when($this->musician_id, fn (Builder $q) => $q->where('user_id', $this->musician_id));
                }),
        ];
    }
}
