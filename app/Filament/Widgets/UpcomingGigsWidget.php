<?php

namespace App\Filament\Widgets;

use App\Enums\GigStatus;
use App\Models\Gig;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UpcomingGigsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $count = Gig::query()
            ->where('status', GigStatus::Active)
            ->whereBetween('date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();

        return [
            Stat::make('Upcoming Gigs', $count)
                ->description('Active gigs in the next 7 days')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}
