<?php

namespace App\Filament\Resources\Gigs\Schemas;

use App\Enums\GigStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date')
                            ->required()
                            ->native(false),
                        Select::make('status')
                            ->options(GigStatus::class)
                            ->default(GigStatus::Draft)
                            ->required(),
                        Select::make('region_id')
                            ->relationship('region', 'name')
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns(2),

                Section::make('Times')
                    ->schema([
                        TimePicker::make('call_time')
                            ->required()
                            ->seconds(false),
                        TimePicker::make('performance_time')
                            ->seconds(false),
                        TimePicker::make('end_time')
                            ->seconds(false),
                    ])
                    ->columns(3),

                Section::make('Venue')
                    ->schema([
                        TextInput::make('venue_name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('venue_address')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Client Contact')
                    ->schema([
                        TextInput::make('client_contact_name')
                            ->maxLength(255),
                        TextInput::make('client_contact_phone')
                            ->maxLength(255),
                        TextInput::make('client_contact_email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Details')
                    ->schema([
                        Textarea::make('dress_code')
                            ->rows(2),
                        Textarea::make('notes')
                            ->rows(3),
                        TextInput::make('pay_info')
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Section::make('Attachments')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('attachments')
                            ->collection('attachments')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf'])
                            ->downloadable()
                            ->openable()
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
