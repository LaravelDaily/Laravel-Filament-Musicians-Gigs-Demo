<?php

use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;
use App\Notifications\GigAssignmentDeclined;
use Illuminate\Support\Facades\Notification;

test('it sends notification when assignment declined', function () {
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

test('it sends to all admin users', function () {
    Notification::fake();

    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($admin1, GigAssignmentDeclined::class);
    Notification::assertSentTo($admin2, GigAssignmentDeclined::class);
});

test('it does not send to inactive admins', function () {
    Notification::fake();

    $activeAdmin = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->inactive()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($activeAdmin, GigAssignmentDeclined::class);
    Notification::assertNotSentTo($inactiveAdmin, GigAssignmentDeclined::class);
});

test('it does not send to musicians', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($admin, GigAssignmentDeclined::class);
    Notification::assertNotSentTo($otherMusician, GigAssignmentDeclined::class);
});

test('it includes gig name in notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create(['name' => 'Jazz Festival']);
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($admin, GigAssignmentDeclined::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains($mail->subject, 'Jazz Festival')
            && str_contains(implode(' ', $mail->introLines), 'Jazz Festival');
    });
});

test('it includes gig date in notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create(['date' => '2025-06-15']);
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($admin, GigAssignmentDeclined::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains(implode(' ', $mail->introLines), 'June 15, 2025');
    });
});

test('it includes musician name in notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create(['name' => 'John Doe']);
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($admin, GigAssignmentDeclined::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains(implode(' ', $mail->introLines), 'John Doe');
    });
});

test('it includes instrument in notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create(['name' => 'Saxophone']);
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($admin, GigAssignmentDeclined::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains(implode(' ', $mail->introLines), 'Saxophone');
    });
});

test('it includes decline reason when provided', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig), [
        'reason' => 'Schedule conflict with another event',
    ]);

    Notification::assertSentTo($admin, GigAssignmentDeclined::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains(implode(' ', $mail->introLines), 'Schedule conflict with another event');
    });
});

test('it omits reason line when not provided', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($admin, GigAssignmentDeclined::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return ! str_contains(implode(' ', $mail->introLines), '**Reason:**');
    });
});

test('it includes link to admin panel', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.decline', $gig));

    Notification::assertSentTo($admin, GigAssignmentDeclined::class, function ($notification) use ($gig) {
        $mail = $notification->toMail($notification->assignment->user);

        return $mail->actionText === 'View Gig in Admin'
            && str_contains($mail->actionUrl, '/admin/gigs/'.$gig->id);
    });
});

test('it queues the notification', function () {
    $notification = new GigAssignmentDeclined(
        GigAssignment::factory()->create()
    );

    expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});
