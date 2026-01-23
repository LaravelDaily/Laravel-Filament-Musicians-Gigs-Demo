<?php

namespace App\Filament\Resources\Gigs\Pages;

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Gigs\GigResource;
use App\Models\AssignmentStatusLog;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewGig extends ViewRecord
{
    protected static string $resource = GigResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('date')
                                    ->date('l, F j, Y'),
                                TextEntry::make('status')
                                    ->badge(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('region.name')
                                    ->label('Region')
                                    ->default('Not assigned'),
                            ]),
                    ]),

                Section::make('Times')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('call_time')
                                    ->time('g:i A'),
                                TextEntry::make('performance_time')
                                    ->time('g:i A')
                                    ->default('Not set'),
                                TextEntry::make('end_time')
                                    ->time('g:i A')
                                    ->default('Not set'),
                            ]),
                    ]),

                Section::make('Venue')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('venue_name'),
                                TextEntry::make('venue_address')
                                    ->url(fn (Gig $record): string => 'https://www.google.com/maps/search/'.urlencode($record->venue_address))
                                    ->openUrlInNewTab(),
                            ]),
                    ]),

                Section::make('Client Contact')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('client_contact_name')
                                    ->default('Not provided'),
                                TextEntry::make('client_contact_phone')
                                    ->default('Not provided'),
                                TextEntry::make('client_contact_email')
                                    ->default('Not provided'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('dress_code')
                                    ->default('Not specified'),
                                TextEntry::make('pay_info')
                                    ->label('Pay Information')
                                    ->default('Not specified'),
                            ]),
                        TextEntry::make('notes')
                            ->default('No notes'),
                    ])
                    ->collapsible(),

                Section::make('Attachments')
                    ->schema([
                        TextEntry::make('attachments_list')
                            ->label('')
                            ->state(function (Gig $record): string {
                                $media = $record->getMedia('attachments');
                                if ($media->isEmpty()) {
                                    return 'No attachments';
                                }

                                return $media->map(fn ($m) => "[{$m->file_name}]({$m->getUrl()})")->implode("\n\n");
                            })
                            ->markdown(),
                    ])
                    ->collapsible(),

                Section::make('Assignments Summary')
                    ->schema([
                        TextEntry::make('staffing_summary')
                            ->label('Staffing Status')
                            ->state(function (Gig $record): string {
                                $total = $record->assignments()->count();
                                $accepted = $record->assignments()->where('status', AssignmentStatus::Accepted)->count();
                                $pending = $record->assignments()->where('status', AssignmentStatus::Pending)->count();
                                $declined = $record->assignments()->where('status', AssignmentStatus::Declined)->count();
                                $subouts = $record->assignments()->where('status', AssignmentStatus::SuboutRequested)->count();

                                $parts = [];
                                if ($pending > 0) {
                                    $parts[] = "{$pending} pending";
                                }
                                if ($accepted > 0) {
                                    $parts[] = "{$accepted} accepted";
                                }
                                if ($declined > 0) {
                                    $parts[] = "{$declined} declined";
                                }
                                if ($subouts > 0) {
                                    $parts[] = "{$subouts} sub-out";
                                }

                                return $total === 0 ? 'No assignments' : implode(', ', $parts);
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulkAssign')
                ->label('Bulk Assign')
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('primary')
                ->visible(fn (): bool => ! $this->record->trashed())
                ->schema([
                    Repeater::make('assignments')
                        ->schema([
                            Select::make('user_id')
                                ->label('Musician')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    $existingUserIds = $this->record
                                        ->assignments()
                                        ->pluck('user_id')
                                        ->toArray();

                                    return User::query()
                                        ->where('role', UserRole::Musician)
                                        ->where('is_active', true)
                                        ->when(! empty($existingUserIds), fn ($q) => $q->whereNotIn('id', $existingUserIds))
                                        ->pluck('name', 'id');
                                })
                                ->distinct()
                                ->live(),
                            Select::make('instrument_id')
                                ->label('Instrument')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(Instrument::pluck('name', 'id')),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->defaultItems(1)
                        ->addActionLabel('Add another musician'),
                ])
                ->action(function (array $data): void {
                    $existingUserIds = $this->record
                        ->assignments()
                        ->pluck('user_id')
                        ->toArray();

                    $created = 0;
                    $skipped = 0;

                    foreach ($data['assignments'] as $assignment) {
                        if (in_array($assignment['user_id'], $existingUserIds)) {
                            $skipped++;

                            continue;
                        }

                        $newAssignment = GigAssignment::create([
                            'gig_id' => $this->record->id,
                            'user_id' => $assignment['user_id'],
                            'instrument_id' => $assignment['instrument_id'],
                            'status' => AssignmentStatus::Pending,
                        ]);

                        AssignmentStatusLog::create([
                            'gig_assignment_id' => $newAssignment->id,
                            'old_status' => null,
                            'new_status' => AssignmentStatus::Pending->value,
                            'reason' => 'Bulk assignment created',
                            'changed_by_user_id' => auth()->id(),
                            'created_at' => now(),
                        ]);

                        $existingUserIds[] = $assignment['user_id'];
                        $created++;
                    }

                    if ($created > 0) {
                        Notification::make()
                            ->success()
                            ->title('Musicians assigned')
                            ->body("{$created} musician(s) assigned".($skipped > 0 ? ", {$skipped} skipped (already assigned)" : ''))
                            ->send();
                    } else {
                        Notification::make()
                            ->warning()
                            ->title('No musicians assigned')
                            ->body('All selected musicians were already assigned to this gig.')
                            ->send();
                    }
                }),
            EditAction::make(),
            Action::make('printWorksheet')
                ->label('Print Worksheet')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('gray')
                ->url(fn (): string => route('admin.gigs.worksheet', ['gig' => $this->record]))
                ->openUrlInNewTab(),
            Action::make('cancel')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancel Gig')
                ->modalDescription('Are you sure you want to cancel this gig?')
                ->visible(fn (): bool => $this->record->status !== GigStatus::Cancelled && ! $this->record->trashed())
                ->action(function (): void {
                    $this->record->update(['status' => GigStatus::Cancelled]);

                    Notification::make()
                        ->success()
                        ->title('Gig cancelled')
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
                        ->body('Please update the date.')
                        ->send();

                    redirect(GigResource::getUrl('edit', ['record' => $newGig]));
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
