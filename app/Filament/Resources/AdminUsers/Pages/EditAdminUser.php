<?php

namespace App\Filament\Resources\AdminUsers\Pages;

use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_active')
                ->label(fn (): string => $this->record->is_active ? 'Deactivate' : 'Activate')
                ->icon(fn (): string => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn (): string => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn (): string => $this->record->is_active ? 'Deactivate Admin User' : 'Activate Admin User')
                ->modalDescription(fn (): string => $this->record->is_active
                    ? 'Are you sure you want to deactivate this admin user? They will no longer be able to access the admin panel.'
                    : 'Are you sure you want to activate this admin user? They will be able to access the admin panel again.')
                ->hidden(fn (): bool => $this->record->id === auth()->id())
                ->action(function (): void {
                    /** @var User $admin */
                    $admin = $this->record;

                    if ($admin->is_active) {
                        $activeAdminCount = User::query()->admins()->active()->count();

                        if ($activeAdminCount <= 1) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot deactivate')
                                ->body('At least one admin user must remain active.')
                                ->send();

                            return;
                        }
                    }

                    $wasActive = $admin->is_active;
                    $admin->update(['is_active' => ! $wasActive]);

                    Notification::make()
                        ->success()
                        ->title($wasActive ? 'Admin user deactivated' : 'Admin user activated')
                        ->send();

                    $this->refreshFormData(['is_active']);
                }),

            DeleteAction::make()
                ->hidden(fn (): bool => $this->record->id === auth()->id())
                ->before(function (DeleteAction $action): void {
                    /** @var User $admin */
                    $admin = $this->record;

                    if ($admin->is_active) {
                        $activeAdminCount = User::query()->admins()->active()->count();

                        if ($activeAdminCount <= 1) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete')
                                ->body('At least one admin user must remain active. You cannot delete the only active admin.')
                                ->send();

                            $action->cancel();
                        }
                    }
                }),
        ];
    }
}
