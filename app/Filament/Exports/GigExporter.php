<?php

namespace App\Filament\Exports;

use App\Enums\AssignmentStatus;
use App\Models\Gig;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class GigExporter extends Exporter
{
    protected static ?string $model = Gig::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('date')
                ->formatStateUsing(fn (?Gig $record): string => $record?->date?->format('Y-m-d') ?? ''),
            ExportColumn::make('name'),
            ExportColumn::make('venue_name')
                ->label('Venue'),
            ExportColumn::make('venue_address')
                ->label('Address'),
            ExportColumn::make('region.name')
                ->label('Region'),
            ExportColumn::make('status')
                ->formatStateUsing(fn (?Gig $record): string => $record?->status?->getLabel() ?? ''),
            ExportColumn::make('staffing')
                ->label('Staffing')
                ->state(function (?Gig $record): string {
                    if (! $record) {
                        return '';
                    }

                    $total = $record->assignments()->count();
                    $accepted = $record->assignments()
                        ->where('status', AssignmentStatus::Accepted)
                        ->count();

                    return "{$accepted}/{$total}";
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your gig export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
