<?php

use App\Enums\AssignmentStatus;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\User;
use App\Notifications\GigAssignmentDeclined;
use Illuminate\Support\Facades\Notification;

test('it can decline pending assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.decline', $gig));

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('success');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Declined);
});

test('it can decline accepted assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.decline', $gig));

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('success');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Declined);
});

test('it cannot decline already declined assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->declined()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.decline', $gig));

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('error');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Declined);
});

test('it cannot decline sub-out requested assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->suboutRequested()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.decline', $gig));

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('error');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::SuboutRequested);
});

test('it sets status to declined', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($user)->post(route('portal.gigs.decline', $gig));

    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Declined);
});

test('it stores decline reason when provided', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($user)->post(route('portal.gigs.decline', $gig), [
        'reason' => 'I have a prior commitment',
    ]);

    expect($assignment->fresh()->decline_reason)->toBe('I have a prior commitment');
});

test('it allows empty reason', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($user)->post(route('portal.gigs.decline', $gig), [
        'reason' => '',
    ]);

    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Declined);
    expect($assignment->fresh()->decline_reason)->toBeNull();
});

test('it sets responded_at timestamp', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
        'responded_at' => null,
    ]);

    $this->freezeTime();

    $this->actingAs($user)->post(route('portal.gigs.decline', $gig));

    expect($assignment->fresh()->responded_at)->not->toBeNull();
    expect($assignment->fresh()->responded_at->toDateTimeString())->toBe(now()->toDateTimeString());
});

test('it creates audit log entry', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($user)->post(route('portal.gigs.decline', $gig), [
        'reason' => 'Schedule conflict',
    ]);

    $this->assertDatabaseHas('assignment_status_logs', [
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Declined->value,
        'reason' => 'Schedule conflict',
        'changed_by_user_id' => $user->id,
    ]);
});

test('it creates audit log entry for accepted to declined', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($user)->post(route('portal.gigs.decline', $gig));

    $this->assertDatabaseHas('assignment_status_logs', [
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Accepted->value,
        'new_status' => AssignmentStatus::Declined->value,
        'changed_by_user_id' => $user->id,
    ]);
});

test('it redirects with success message', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.decline', $gig));

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('success', 'You have declined this gig assignment.');
});

test('it cannot decline assignment for other musician', function () {
    $user = User::factory()->musician()->create();
    $otherUser = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $otherUser->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.decline', $gig));

    $response->assertForbidden();
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Pending);
});

test('it validates reason max length', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.decline', $gig), [
        'reason' => str_repeat('a', 1001),
    ]);

    $response->assertSessionHasErrors('reason');
});

test('it requires authentication', function () {
    $gig = Gig::factory()->active()->future()->create();

    $response = $this->post(route('portal.gigs.decline', $gig));

    $response->assertRedirect(route('login'));
});

test('it requires musician role', function () {
    $admin = User::factory()->admin()->create();
    $gig = Gig::factory()->active()->future()->create();

    $response = $this->actingAs($admin)->post(route('portal.gigs.decline', $gig));

    $response->assertForbidden();
});

test('it sends notification to admins', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($admin, GigAssignmentDeclined::class);
});
