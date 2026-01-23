<?php

namespace App\Filament\Resources\AdminUsers\Tables;

use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class AdminUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All Admins')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to deactivate the selected admin users? They will no longer be able to access the admin panel.')
                        ->action(function (Collection $records): void {
                            $currentUserId = auth()->id();
                            $activeAdminCount = User::query()->admins()->active()->count();

                            $recordsExcludingSelf = $records->filter(fn (User $user) => $user->id !== $currentUserId);
                            $activeToDeactivate = $recordsExcludingSelf->filter(fn (User $user) => $user->is_active)->count();

                            if ($activeAdminCount - $activeToDeactivate < 1) {
                                Notification::make()
                                    ->danger()
                                    ->title('Cannot deactivate')
                                    ->body('At least one admin user must remain active.')
                                    ->send();

                                return;
                            }

                            if ($records->contains('id', $currentUserId)) {
                                Notification::make()
                                    ->warning()
                                    ->title('Cannot deactivate yourself')
                                    ->body('You cannot deactivate your own account. Other selected admins have been deactivated.')
                                    ->send();
                            }

                            $recordsExcludingSelf->each(fn (User $user) => $user->update(['is_active' => false]));

                            if ($recordsExcludingSelf->isNotEmpty()) {
                                Notification::make()
                                    ->success()
                                    ->title('Admin users deactivated')
                                    ->body('Selected admin users have been deactivated.')
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (User $user) => $user->update(['is_active' => true]));

                            Notification::make()
                                ->success()
                                ->title('Admin users activated')
                                ->body('Selected admin users have been activated.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
