<?php

namespace App\Filament\Widgets;

use App\Enums\AssignmentStatus;
use App\Models\AssignmentStatusLog;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $declines = AssignmentStatusLog::query()
            ->where('new_status', AssignmentStatus::Declined->value)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $subouts = AssignmentStatusLog::query()
            ->where('new_status', AssignmentStatus::SuboutRequested->value)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return [
            Stat::make('Recent Declines', $declines)
                ->description('In the last 24 hours')
                ->descriptionIcon(Heroicon::XMark)
                ->color($declines > 0 ? 'danger' : 'gray'),
            Stat::make('Recent Sub-outs', $subouts)
                ->description('In the last 24 hours')
                ->descriptionIcon(Heroicon::ArrowPath)
                ->color($subouts > 0 ? 'warning' : 'gray'),
        ];
    }
}
