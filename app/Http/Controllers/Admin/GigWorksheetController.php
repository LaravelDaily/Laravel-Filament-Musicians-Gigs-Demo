<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssignmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Gig;
use Illuminate\View\View;

class GigWorksheetController extends Controller
{
    public function show(Gig $gig): View
    {
        $gig->load([
            'region',
            'assignments' => fn ($query) => $query
                ->whereIn('status', [AssignmentStatus::Accepted, AssignmentStatus::Pending])
                ->with(['user', 'instrument']),
        ]);

        return view('admin.gig-worksheet', compact('gig'));
    }
}
