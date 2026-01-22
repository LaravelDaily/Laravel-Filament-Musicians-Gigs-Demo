<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum GigStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Active = 'active';
    case Cancelled = 'cancelled';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::Active => 'heroicon-o-check-circle',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
