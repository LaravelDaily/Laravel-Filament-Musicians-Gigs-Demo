<?php

namespace App\Filament\Exports;

use App\Models\GigAssignment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class GigAssignmentExporter extends Exporter
{
    protected static ?string $model = GigAssignment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('gig.date')
                ->label('Gig Date')
                ->formatStateUsing(fn (?GigAssignment $record): string => $record?->gig?->date?->format('Y-m-d') ?? ''),
            ExportColumn::make('gig.name')
                ->label('Gig Name'),
            ExportColumn::make('user.name')
                ->label('Musician'),
            ExportColumn::make('user.email')
                ->label('Email'),
            ExportColumn::make('instrument.name')
                ->label('Instrument'),
            ExportColumn::make('status')
                ->formatStateUsing(fn (?GigAssignment $record): string => $record?->status?->getLabel() ?? ''),
            ExportColumn::make('pay_amount')
                ->label('Pay Amount'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your gig assignment export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
