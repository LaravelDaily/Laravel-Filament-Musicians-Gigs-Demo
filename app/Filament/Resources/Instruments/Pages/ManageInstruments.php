<?php

namespace App\Filament\Resources\Instruments\Pages;

use App\Filament\Resources\Instruments\InstrumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageInstruments extends ManageRecords
{
    protected static string $resource = InstrumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
