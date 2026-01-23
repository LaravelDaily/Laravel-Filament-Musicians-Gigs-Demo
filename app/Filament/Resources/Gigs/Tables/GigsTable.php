<?php

namespace App\Filament\Resources\Gigs\Tables;

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\GigResource;
use App\Models\Gig;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date('M j, Y')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('venue_name')
                    ->label('Venue')
                    ->searchable(),
                TextColumn::make('region.name')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('staffing')
                    ->label('Staffing')
                    ->state(function (Gig $record): string {
                        $total = $record->assignments()->count();
                        $accepted = $record->assignments()
                            ->where('status', AssignmentStatus::Accepted)
                            ->count();

                        return "{$accepted}/{$total}";
                    })
                    ->color(function (Gig $record): string {
                        $total = $record->assignments()->count();
                        if ($total === 0) {
                            return 'gray';
                        }

                        $accepted = $record->assignments()
                            ->where('status', AssignmentStatus::Accepted)
                            ->count();
                        $pending = $record->assignments()
                            ->where('status', AssignmentStatus::Pending)
                            ->count();
                        $subouts = $record->assignments()
                            ->where('status', AssignmentStatus::SuboutRequested)
                            ->count();

                        if ($subouts > 0) {
                            return 'warning';
                        }

                        if ($accepted === $total) {
                            return 'success';
                        }

                        if ($pending > 0) {
                            return 'info';
                        }

                        return 'danger';
                    })
                    ->badge(),
            ])
            ->defaultSort('date', 'asc')
            ->filters([
                Filter::make('upcoming')
                    ->label('Upcoming Only')
                    ->query(fn (Builder $query): Builder => $query->where('date', '>=', now()->toDateString()))
                    ->default(),
                Filter::make('date_range')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                SelectFilter::make('region')
                    ->relationship('region', 'name')
                    ->preload(),
                SelectFilter::make('status')
                    ->options(GigStatus::class),
                SelectFilter::make('staffing')
                    ->label('Staffing Status')
                    ->options([
                        'fully_staffed' => 'Fully Staffed',
                        'needs_musicians' => 'Needs Musicians',
                        'has_pending' => 'Has Pending',
                        'has_subouts' => 'Has Sub-outs',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            'fully_staffed' => $query->whereHas('assignments')
                                ->whereDoesntHave('assignments', fn (Builder $q) => $q->where('status', '!=', AssignmentStatus::Accepted)),
                            'needs_musicians' => $query->whereDoesntHave('assignments')
                                ->orWhereHas('assignments', fn (Builder $q) => $q->whereIn('status', [AssignmentStatus::Declined, AssignmentStatus::SuboutRequested])),
                            'has_pending' => $query->whereHas('assignments', fn (Builder $q) => $q->where('status', AssignmentStatus::Pending)),
                            'has_subouts' => $query->whereHas('assignments', fn (Builder $q) => $q->where('status', AssignmentStatus::SuboutRequested)),
                            default => $query,
                        };
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('cancel')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Gig')
                    ->modalDescription('Are you sure you want to cancel this gig? This action can be undone by editing the gig.')
                    ->visible(fn (Gig $record): bool => $record->status !== GigStatus::Cancelled && ! $record->trashed())
                    ->action(function (Gig $record): void {
                        $record->update(['status' => GigStatus::Cancelled]);

                        Notification::make()
                            ->success()
                            ->title('Gig cancelled')
                            ->body("'{$record->name}' has been cancelled.")
                            ->send();
                    }),
                Action::make('replicate')
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->color('gray')
                    ->visible(fn (Gig $record): bool => ! $record->trashed())
                    ->action(function (Gig $record): void {
                        $newGig = $record->replicate();
                        $newGig->status = GigStatus::Draft;
                        $newGig->save();

                        foreach ($record->getMedia('attachments') as $media) {
                            $media->copy($newGig, 'attachments');
                        }

                        Notification::make()
                            ->success()
                            ->title('Gig duplicated')
                            ->body('The gig has been duplicated. Please set the date.')
                            ->send();

                        redirect(GigResource::getUrl('edit', ['record' => $newGig]));
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
