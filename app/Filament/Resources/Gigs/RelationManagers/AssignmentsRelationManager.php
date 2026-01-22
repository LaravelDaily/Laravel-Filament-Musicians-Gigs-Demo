<?php

namespace App\Filament\Resources\Gigs\RelationManagers;

use App\Enums\AssignmentStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Musicians\MusicianResource;
use App\Models\AssignmentStatusLog;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Assignments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Musician')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function (?GigAssignment $record) {
                        $query = User::query()
                            ->where('role', UserRole::Musician)
                            ->where('is_active', true);

                        $existingUserIds = $this->getOwnerRecord()
                            ->assignments()
                            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                            ->pluck('user_id')
                            ->toArray();

                        if (! empty($existingUserIds)) {
                            $query->whereNotIn('id', $existingUserIds);
                        }

                        return $query->pluck('name', 'id');
                    }),
                Select::make('instrument_id')
                    ->label('Instrument')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(Instrument::pluck('name', 'id')),
                TextInput::make('pay_amount')
                    ->label('Pay Amount')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Musician')
                    ->searchable()
                    ->sortable()
                    ->url(fn (GigAssignment $record): string => MusicianResource::getUrl('edit', ['record' => $record->user_id])),
                TextColumn::make('instrument.name')
                    ->label('Instrument')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('pay_amount')
                    ->label('Pay')
                    ->money('usd')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('responded_at')
                    ->label('Responded')
                    ->dateTime('M j, g:i A')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('subout_reason')
                    ->label('Sub-out Reason')
                    ->limit(30)
                    ->tooltip(fn (GigAssignment $record): ?string => $record->subout_reason)
                    ->visible(fn () => $this->getOwnerRecord()->assignments()->where('status', AssignmentStatus::SuboutRequested)->exists())
                    ->color('warning'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(AssignmentStatus::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['status'] = AssignmentStatus::Pending;

                        return $data;
                    })
                    ->after(function (GigAssignment $record): void {
                        $this->logStatusChange($record, null, AssignmentStatus::Pending, 'Assignment created');
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, GigAssignment $record): array {
                        return $data;
                    }),
                Action::make('findReplacement')
                    ->label('Find Replacement')
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->color('warning')
                    ->visible(fn (GigAssignment $record): bool => $record->status === AssignmentStatus::SuboutRequested)
                    ->form(function (?GigAssignment $record) {
                        if (! $record) {
                            return [];
                        }
                        /** @var Gig $gig */
                        $gig = $this->getOwnerRecord();
                        $instrumentId = $record->instrument_id;
                        $gigDate = $gig->date;

                        $existingUserIds = $gig
                            ->assignments()
                            ->pluck('user_id')
                            ->toArray();

                        $availableMusicians = User::query()
                            ->where('role', UserRole::Musician)
                            ->where('is_active', true)
                            ->whereNotIn('id', $existingUserIds)
                            ->whereHas('instruments', fn ($q) => $q->where('instruments.id', $instrumentId))
                            ->get();

                        $musiciansWithConflicts = [];
                        foreach ($availableMusicians as $musician) {
                            $hasConflict = GigAssignment::query()
                                ->where('user_id', $musician->id)
                                ->whereHas('gig', fn ($q) => $q->where('date', $gigDate))
                                ->exists();

                            if ($hasConflict) {
                                $musiciansWithConflicts[] = $musician->id;
                            }
                        }

                        $options = $availableMusicians->mapWithKeys(function ($musician) use ($musiciansWithConflicts) {
                            $label = $musician->name;
                            if (in_array($musician->id, $musiciansWithConflicts)) {
                                $label .= ' ⚠️ (has conflicting gig)';
                            }

                            return [$musician->id => $label];
                        })->toArray();

                        $components = [
                            Placeholder::make('subout_info')
                                ->label('Sub-out Request')
                                ->content(new HtmlString(
                                    '<div class="text-warning-600 dark:text-warning-400">'.
                                    '<strong>'.$record->user->name.'</strong> requested a sub-out'.
                                    ($record->subout_reason ? ':<br><em>"'.e($record->subout_reason).'"</em>' : '.').
                                    '</div>'
                                )),
                            Placeholder::make('instrument_info')
                                ->label('Required Instrument')
                                ->content($record->instrument->name),
                        ];

                        if (empty($options)) {
                            $components[] = Placeholder::make('no_musicians')
                                ->label('')
                                ->content(new HtmlString(
                                    '<div class="text-danger-600 dark:text-danger-400">'.
                                    'No available musicians found with this instrument who are not already assigned to this gig.'.
                                    '</div>'
                                ));
                        } else {
                            if (! empty($musiciansWithConflicts)) {
                                $components[] = Placeholder::make('conflict_warning')
                                    ->label('')
                                    ->content(new HtmlString(
                                        '<div class="text-warning-600 dark:text-warning-400 text-sm">'.
                                        '⚠️ Some musicians have other gigs scheduled on the same date.'.
                                        '</div>'
                                    ));
                            }

                            $components[] = Select::make('replacement_user_id')
                                ->label('Select Replacement Musician')
                                ->options($options)
                                ->required()
                                ->searchable();
                        }

                        return $components;
                    })
                    ->action(function (array $data, GigAssignment $record): void {
                        if (empty($data['replacement_user_id'])) {
                            return;
                        }

                        /** @var Gig $gig */
                        $gig = $this->getOwnerRecord();

                        $existingAssignment = GigAssignment::query()
                            ->where('gig_id', $gig->id)
                            ->where('user_id', $data['replacement_user_id'])
                            ->exists();

                        if ($existingAssignment) {
                            Notification::make()
                                ->danger()
                                ->title('Assignment failed')
                                ->body('This musician is already assigned to this gig.')
                                ->send();

                            return;
                        }

                        $newAssignment = GigAssignment::create([
                            'gig_id' => $gig->id,
                            'user_id' => $data['replacement_user_id'],
                            'instrument_id' => $record->instrument_id,
                            'status' => AssignmentStatus::Pending,
                            'pay_amount' => $record->pay_amount,
                            'notes' => 'Replacement for '.$record->user->name,
                        ]);

                        $this->logStatusChange($newAssignment, null, AssignmentStatus::Pending, 'Replacement assignment for sub-out');

                        $replacementMusician = User::find($data['replacement_user_id']);

                        Notification::make()
                            ->success()
                            ->title('Replacement assigned')
                            ->body("{$replacementMusician->name} has been assigned as replacement.")
                            ->send();
                    }),
                Action::make('changeStatus')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->form([
                        Select::make('status')
                            ->options(AssignmentStatus::class)
                            ->required(),
                        Textarea::make('reason')
                            ->label('Reason (optional)')
                            ->rows(2),
                    ])
                    ->fillForm(fn (GigAssignment $record): array => [
                        'status' => $record->status,
                    ])
                    ->action(function (array $data, GigAssignment $record): void {
                        $oldStatus = $record->status;
                        $newStatus = $data['status'] instanceof AssignmentStatus
                            ? $data['status']
                            : AssignmentStatus::from($data['status']);

                        if ($oldStatus === $newStatus) {
                            Notification::make()
                                ->warning()
                                ->title('No change')
                                ->body('Status is already '.$newStatus->getLabel())
                                ->send();

                            return;
                        }

                        $record->update([
                            'status' => $newStatus,
                            'responded_at' => now(),
                            'subout_reason' => $newStatus === AssignmentStatus::SuboutRequested ? ($data['reason'] ?? null) : $record->subout_reason,
                            'decline_reason' => $newStatus === AssignmentStatus::Declined ? ($data['reason'] ?? null) : $record->decline_reason,
                        ]);

                        $this->logStatusChange($record, $oldStatus, $newStatus, $data['reason'] ?? null);

                        Notification::make()
                            ->success()
                            ->title('Status updated')
                            ->body("Changed from {$oldStatus->getLabel()} to {$newStatus->getLabel()}")
                            ->send();
                    }),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (GigAssignment $record): void {
                        $this->logStatusChange($record, $record->status, null, 'Assignment removed');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->before(function ($records): void {
                            foreach ($records as $record) {
                                $this->logStatusChange($record, $record->status, null, 'Assignment removed (bulk)');
                            }
                        }),
                ]),
            ])
            ->defaultSort('user.name');
    }

    protected function logStatusChange(
        GigAssignment $assignment,
        ?AssignmentStatus $oldStatus,
        ?AssignmentStatus $newStatus,
        ?string $reason = null
    ): void {
        AssignmentStatusLog::create([
            'gig_assignment_id' => $assignment->id,
            'old_status' => $oldStatus?->value,
            'new_status' => $newStatus?->value ?? 'deleted',
            'reason' => $reason,
            'changed_by_user_id' => auth()->id(),
            'created_at' => now(),
        ]);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->assignments()->count();

        return $count > 0 ? (string) $count : null;
    }
}
