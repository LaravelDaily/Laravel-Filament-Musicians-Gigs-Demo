<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveMusiciansWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $count = User::query()
            ->musicians()
            ->active()
            ->count();

        return [
            Stat::make('Active Musicians', $count)
                ->description('Total musicians in roster')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
        ];
    }
}
