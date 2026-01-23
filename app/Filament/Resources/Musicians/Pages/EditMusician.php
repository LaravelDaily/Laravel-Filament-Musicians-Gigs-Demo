<?php

namespace App\Filament\Resources\Musicians\Pages;

use App\Filament\Resources\Musicians\MusicianResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditMusician extends EditRecord
{
    protected static string $resource = MusicianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_active')
                ->label(fn (): string => $this->record->is_active ? 'Deactivate' : 'Activate')
                ->icon(fn (): Heroicon => $this->record->is_active ? Heroicon::OutlinedXCircle : Heroicon::OutlinedCheckCircle)
                ->color(fn (): string => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn (): string => $this->record->is_active ? 'Deactivate Musician' : 'Activate Musician')
                ->modalDescription(fn (): string => $this->record->is_active
                    ? 'Are you sure you want to deactivate this musician? They will no longer be able to log in.'
                    : 'Are you sure you want to activate this musician? They will be able to log in again.')
                ->action(function (): void {
                    /** @var User $musician */
                    $musician = $this->record;
                    $wasActive = $musician->is_active;
                    $musician->update(['is_active' => ! $wasActive]);

                    Notification::make()
                        ->success()
                        ->title($wasActive ? 'Musician deactivated' : 'Musician activated')
                        ->send();

                    $this->refreshFormData(['is_active']);
                }),
        ];
    }
}
