<?php

namespace App\Filament\Resources\AdminUsers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Admin Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Account Information')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Member Since')
                            ->state(fn ($record): string => $record?->created_at?->format('F j, Y') ?? '-'),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->state(fn ($record): string => $record?->updated_at?->format('F j, Y g:i A') ?? '-'),
                    ])
                    ->columns(2)
                    ->hiddenOn('create'),
            ]);
    }
}
