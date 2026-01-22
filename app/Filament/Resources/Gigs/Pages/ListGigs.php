<?php

namespace App\Filament\Resources\Gigs\Pages;

use App\Filament\Exports\GigExporter;
use App\Filament\Resources\Gigs\GigResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListGigs extends ListRecords
{
    protected static string $resource = GigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(GigExporter::class),
            CreateAction::make(),
        ];
    }
}
