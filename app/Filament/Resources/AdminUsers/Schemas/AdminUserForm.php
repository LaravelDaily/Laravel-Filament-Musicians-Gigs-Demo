<?php

namespace App\Filament\Resources\AdminUsers\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
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
                        Placeholder::make('created_at')
                            ->label('Member Since')
                            ->content(fn ($record): string => $record?->created_at?->format('F j, Y') ?? '-'),
                        Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn ($record): string => $record?->updated_at?->format('F j, Y g:i A') ?? '-'),
                    ])
                    ->columns(2)
                    ->hiddenOn('create'),
            ]);
    }
}
