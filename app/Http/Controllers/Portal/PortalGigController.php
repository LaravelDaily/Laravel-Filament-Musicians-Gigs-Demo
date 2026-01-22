<?php

namespace App\Http\Controllers\Portal;

use App\Enums\AssignmentStatus;
use App\Models\Gig;
use App\Models\GigAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalGigController extends PortalController
{
    public function show(Gig $gig): View
    {
        $assignment = $this->getAssignmentForCurrentUser($gig);

        abort_if(! $assignment, 403, 'You are not assigned to this gig.');

        $gig->load(['region', 'media']);

        $otherAssignments = $gig->assignments()
            ->with(['user', 'instrument'])
            ->where('id', '!=', $assignment->id)
            ->whereIn('status', [AssignmentStatus::Accepted, AssignmentStatus::Pending, AssignmentStatus::SuboutRequested])
            ->get();

        return view('portal.gig-detail', [
            'gig' => $gig,
            'assignment' => $assignment,
            'otherAssignments' => $otherAssignments,
        ]);
    }

    public function accept(Gig $gig): RedirectResponse
    {
        $assignment = $this->getAssignmentForCurrentUser($gig);

        abort_if(! $assignment, 403, 'You are not assigned to this gig.');

        if ($assignment->status !== AssignmentStatus::Pending) {
            return redirect()
                ->route('portal.gigs.show', $gig)
                ->with('error', 'You can only accept a pending assignment.');
        }

        $oldStatus = $assignment->status;

        $assignment->update([
            'status' => AssignmentStatus::Accepted,
            'responded_at' => now(),
        ]);

        $this->logStatusChange($assignment, $oldStatus, AssignmentStatus::Accepted);

        return redirect()
            ->route('portal.gigs.show', $gig)
            ->with('success', 'You have accepted this gig assignment.');
    }

    public function decline(Request $request, Gig $gig): RedirectResponse
    {
        $assignment = $this->getAssignmentForCurrentUser($gig);

        abort_if(! $assignment, 403, 'You are not assigned to this gig.');

        if (! in_array($assignment->status, [AssignmentStatus::Pending, AssignmentStatus::Accepted])) {
            return redirect()
                ->route('portal.gigs.show', $gig)
                ->with('error', 'You cannot decline this assignment.');
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $assignment->status;

        $assignment->update([
            'status' => AssignmentStatus::Declined,
            'decline_reason' => $request->input('reason'),
            'responded_at' => now(),
        ]);

        $this->logStatusChange($assignment, $oldStatus, AssignmentStatus::Declined, $request->input('reason'));

        // TODO: Send notification to admins (Phase 8)

        return redirect()
            ->route('portal.gigs.show', $gig)
            ->with('success', 'You have declined this gig assignment.');
    }

    private function getAssignmentForCurrentUser(Gig $gig): ?GigAssignment
    {
        return GigAssignment::query()
            ->with('instrument')
            ->where('gig_id', $gig->id)
            ->where('user_id', auth()->id())
            ->first();
    }

    private function logStatusChange(
        GigAssignment $assignment,
        AssignmentStatus $oldStatus,
        AssignmentStatus $newStatus,
        ?string $reason = null
    ): void {
        $assignment->statusLogs()->create([
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
            'reason' => $reason,
            'changed_by_user_id' => auth()->id(),
            'created_at' => now(),
        ]);
    }
}
