<?php

namespace App\Filament\Resources\Gigs\Pages;

use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\GigResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditGig extends EditRecord
{
    protected static string $resource = GigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('cancel')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancel Gig')
                ->modalDescription('Are you sure you want to cancel this gig? This action can be undone by changing the status.')
                ->visible(fn (): bool => $this->record->status !== GigStatus::Cancelled && ! $this->record->trashed())
                ->action(function (): void {
                    $this->record->update(['status' => GigStatus::Cancelled]);

                    Notification::make()
                        ->success()
                        ->title('Gig cancelled')
                        ->body("'{$this->record->name}' has been cancelled.")
                        ->send();

                    $this->refreshFormData(['status']);
                }),
            Action::make('replicate')
                ->icon(Heroicon::OutlinedDocumentDuplicate)
                ->color('gray')
                ->label('Duplicate')
                ->visible(fn (): bool => ! $this->record->trashed())
                ->action(function (): void {
                    $newGig = $this->record->replicate();
                    $newGig->status = GigStatus::Draft;
                    $newGig->save();

                    foreach ($this->record->getMedia('attachments') as $media) {
                        $media->copy($newGig, 'attachments');
                    }

                    Notification::make()
                        ->success()
                        ->title('Gig duplicated')
                        ->body('The gig has been duplicated. Please update the date.')
                        ->send();

                    redirect(GigResource::getUrl('edit', ['record' => $newGig]));
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
