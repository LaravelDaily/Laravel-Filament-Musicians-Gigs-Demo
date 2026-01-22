<?php

namespace App\Http\Controllers\Portal;

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Models\GigAssignment;
use Illuminate\Contracts\View\View;

class PortalDashboardController extends PortalController
{
    public function index(): View
    {
        $assignments = GigAssignment::query()
            ->with(['gig.region', 'instrument'])
            ->where('user_id', auth()->id())
            ->whereHas('gig', function ($query): void {
                $query->upcoming()
                    ->where('status', GigStatus::Active);
            })
            ->where('status', '!=', AssignmentStatus::Declined)
            ->get()
            ->sortBy('gig.date');

        return view('portal.dashboard', [
            'assignments' => $assignments,
        ]);
    }
}
