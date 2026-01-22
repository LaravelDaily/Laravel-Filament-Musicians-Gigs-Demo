<?php

use App\Enums\AssignmentStatus;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\User;
use App\Notifications\SubOutRequested;
use Illuminate\Support\Facades\Notification;

test('it can request sub-out for accepted assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Family emergency',
    ]);

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('success');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::SuboutRequested);
});

test('it cannot request sub-out for pending assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Some reason',
    ]);

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('error');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Pending);
});

test('it cannot request sub-out for declined assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->declined()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Some reason',
    ]);

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('error');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Declined);
});

test('it cannot request sub-out for already sub-out requested assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->suboutRequested()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Some reason',
    ]);

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('error');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::SuboutRequested);
});

test('it requires reason for sub-out', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => '',
    ]);

    $response->assertSessionHasErrors('reason');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Accepted);
});

test('it validates reason is not empty', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.subout', $gig));

    $response->assertSessionHasErrors('reason');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Accepted);
});

test('it validates reason max length', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => str_repeat('a', 1001),
    ]);

    $response->assertSessionHasErrors('reason');
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Accepted);
});

test('it sets status to subout requested', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Medical appointment',
    ]);

    expect($assignment->fresh()->status)->toBe(AssignmentStatus::SuboutRequested);
});

test('it stores subout reason', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Family emergency',
    ]);

    expect($assignment->fresh()->subout_reason)->toBe('Family emergency');
});

test('it sets responded_at timestamp', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
        'responded_at' => null,
    ]);

    $this->freezeTime();

    $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Work conflict',
    ]);

    expect($assignment->fresh()->responded_at)->not->toBeNull();
    expect($assignment->fresh()->responded_at->toDateTimeString())->toBe(now()->toDateTimeString());
});

test('it creates audit log entry', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Double booked',
    ]);

    $this->assertDatabaseHas('assignment_status_logs', [
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Accepted->value,
        'new_status' => AssignmentStatus::SuboutRequested->value,
        'reason' => 'Double booked',
        'changed_by_user_id' => $user->id,
    ]);
});

test('it redirects with success message', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Need to cancel',
    ]);

    $response->assertRedirect(route('portal.gigs.show', $gig));
    $response->assertSessionHas('success', 'Your sub-out request has been submitted. An admin will contact you soon.');
});

test('it cannot request sub-out for other musician assignment', function () {
    $user = User::factory()->musician()->create();
    $otherUser = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $otherUser->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Some reason',
    ]);

    $response->assertForbidden();
    expect($assignment->fresh()->status)->toBe(AssignmentStatus::Accepted);
});

test('it requires authentication', function () {
    $gig = Gig::factory()->active()->future()->create();

    $response = $this->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Some reason',
    ]);

    $response->assertRedirect(route('login'));
});

test('it requires musician role', function () {
    $admin = User::factory()->admin()->create();
    $gig = Gig::factory()->active()->future()->create();

    $response = $this->actingAs($admin)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Some reason',
    ]);

    $response->assertForbidden();
});

test('it sends urgent notification to admins', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Family emergency',
    ]);

    Notification::assertSentTo($admin, SubOutRequested::class);
});
