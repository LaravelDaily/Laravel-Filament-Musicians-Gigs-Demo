<?php

namespace App\Filament\Widgets;

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Models\Gig;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NeedsAttentionWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $gigsWithPending = Gig::query()
            ->where('status', GigStatus::Active)
            ->upcoming()
            ->whereHas('assignments', fn ($query) => $query->where('status', AssignmentStatus::Pending))
            ->count();

        $gigsWithSubouts = Gig::query()
            ->where('status', GigStatus::Active)
            ->upcoming()
            ->whereHas('assignments', fn ($query) => $query->where('status', AssignmentStatus::SuboutRequested))
            ->count();

        return [
            Stat::make('Pending Responses', $gigsWithPending)
                ->description('Gigs awaiting musician response')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Sub-out Requests', $gigsWithSubouts)
                ->description('Gigs needing replacement')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('danger'),
        ];
    }
}
