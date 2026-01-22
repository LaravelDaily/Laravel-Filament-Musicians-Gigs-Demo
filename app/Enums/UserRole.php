<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum UserRole: string implements HasColor, HasIcon, HasLabel
{
    case Admin = 'admin';
    case Musician = 'musician';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Musician => 'Musician',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Musician => 'primary',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Admin => 'heroicon-o-shield-check',
            self::Musician => 'heroicon-o-musical-note',
        };
    }
}
