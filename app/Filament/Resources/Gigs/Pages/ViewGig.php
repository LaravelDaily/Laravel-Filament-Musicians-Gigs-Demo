<?php

namespace App\Filament\Resources\Gigs\Pages;

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\GigResource;
use App\Filament\Resources\Musicians\MusicianResource;
use App\Models\Gig;
use App\Models\GigAssignment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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

                Section::make('Assignments')
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
                        RepeatableEntry::make('assignments')
                            ->label('')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('Musician')
                                            ->url(fn (GigAssignment $record): string => MusicianResource::getUrl('edit', ['record' => $record->user_id])),
                                        TextEntry::make('instrument.name')
                                            ->label('Instrument'),
                                        TextEntry::make('status')
                                            ->badge(),
                                        TextEntry::make('responded_at')
                                            ->label('Responded')
                                            ->dateTime('M j, g:i A')
                                            ->placeholder('—'),
                                        Group::make([
                                            TextEntry::make('subout_reason')
                                                ->label('Sub-out Reason')
                                                ->visible(fn (GigAssignment $record): bool => $record->status === AssignmentStatus::SuboutRequested)
                                                ->color('warning'),
                                            TextEntry::make('decline_reason')
                                                ->label('Decline Reason')
                                                ->visible(fn (GigAssignment $record): bool => $record->status === AssignmentStatus::Declined && $record->decline_reason !== null)
                                                ->color('danger'),
                                        ]),
                                    ]),
                            ])
                            ->contained(false),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('cancel')
                ->icon('heroicon-o-x-circle')
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
                ->icon('heroicon-o-document-duplicate')
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
