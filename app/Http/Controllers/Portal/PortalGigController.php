<?php

namespace App\Http\Controllers\Portal;

use App\Enums\AssignmentStatus;
use App\Enums\UserRole;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\User;
use App\Notifications\GigAssignmentDeclined;
use App\Notifications\SubOutRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class PortalGigController extends PortalController
{
    public function past(): View
    {
        $assignments = GigAssignment::query()
            ->with(['gig.region', 'instrument'])
            ->where('user_id', auth()->id())
            ->whereHas('gig', function ($query): void {
                $query->where('date', '<', now()->startOfDay());
            })
            ->join('gigs', 'gig_assignments.gig_id', '=', 'gigs.id')
            ->orderByDesc('gigs.date')
            ->select('gig_assignments.*')
            ->paginate(12);

        return view('portal.past-gigs', [
            'assignments' => $assignments,
        ]);
    }

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

        $this->notifyAdmins(new GigAssignmentDeclined($assignment->load(['gig', 'user', 'instrument'])));

        return redirect()
            ->route('portal.gigs.show', $gig)
            ->with('success', 'You have declined this gig assignment.');
    }

    public function subout(Request $request, Gig $gig): RedirectResponse
    {
        $assignment = $this->getAssignmentForCurrentUser($gig);

        abort_if(! $assignment, 403, 'You are not assigned to this gig.');

        if ($assignment->status !== AssignmentStatus::Accepted) {
            return redirect()
                ->route('portal.gigs.show', $gig)
                ->with('error', 'You can only request a sub-out for an accepted assignment.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $oldStatus = $assignment->status;

        $assignment->update([
            'status' => AssignmentStatus::SuboutRequested,
            'subout_reason' => $request->input('reason'),
            'responded_at' => now(),
        ]);

        $this->logStatusChange($assignment, $oldStatus, AssignmentStatus::SuboutRequested, $request->input('reason'));

        $this->notifyAdmins(new SubOutRequested($assignment->load(['gig', 'user', 'instrument'])));

        return redirect()
            ->route('portal.gigs.show', $gig)
            ->with('success', 'Your sub-out request has been submitted. An admin will contact you soon.');
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

    private function notifyAdmins(\Illuminate\Notifications\Notification $notification): void
    {
        $admins = User::where('role', UserRole::Admin)->where('is_active', true)->get();

        Notification::send($admins, $notification);
    }
}
