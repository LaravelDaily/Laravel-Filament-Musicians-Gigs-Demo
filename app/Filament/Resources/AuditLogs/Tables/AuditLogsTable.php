<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Enums\AssignmentStatus;
use App\Models\Gig;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('assignment.gig.name')
                    ->label('Gig')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignment.user.name')
                    ->label('Musician')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('old_status')
                    ->label('Old Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): ?string => $state ? AssignmentStatus::tryFrom($state)?->getLabel() : null)
                    ->color(fn (?string $state): ?string => $state ? AssignmentStatus::tryFrom($state)?->getColor() : null),
                TextColumn::make('new_status')
                    ->label('New Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): ?string => $state ? AssignmentStatus::tryFrom($state)?->getLabel() : null)
                    ->color(fn (?string $state): ?string => $state ? AssignmentStatus::tryFrom($state)?->getColor() : null),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('—'),
                TextColumn::make('changedBy.name')
                    ->label('Changed By')
                    ->placeholder('System'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')
                            ->label('From'),
                        DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From '.$data['from'];
                        }

                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Until '.$data['until'];
                        }

                        return $indicators;
                    }),
                SelectFilter::make('gig')
                    ->label('Gig')
                    ->relationship('assignment.gig', 'name')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn (Gig $record) => "{$record->name} ({$record->date->format('M j, Y')})"),
                SelectFilter::make('musician')
                    ->label('Musician')
                    ->relationship('assignment.user', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
