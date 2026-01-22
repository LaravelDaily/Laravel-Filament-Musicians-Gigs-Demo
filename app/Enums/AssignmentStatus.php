<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AssignmentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case SuboutRequested = 'subout_requested';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::SuboutRequested => 'Sub-out Requested',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted => 'success',
            self::Declined => 'danger',
            self::SuboutRequested => 'info',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Accepted => 'heroicon-o-check',
            self::Declined => 'heroicon-o-x-mark',
            self::SuboutRequested => 'heroicon-o-arrow-path',
        };
    }
}
