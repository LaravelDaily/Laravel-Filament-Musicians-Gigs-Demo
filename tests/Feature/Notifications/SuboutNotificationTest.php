<?php

use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;
use App\Notifications\SubOutRequested;
use Illuminate\Support\Facades\Notification;

test('it sends notification when sub-out requested', function () {
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

test('it sends to all admin users', function () {
    Notification::fake();

    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Family emergency',
    ]);

    Notification::assertSentTo($admin1, SubOutRequested::class);
    Notification::assertSentTo($admin2, SubOutRequested::class);
});

test('it does not send to inactive admins', function () {
    Notification::fake();

    $activeAdmin = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->inactive()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Family emergency',
    ]);

    Notification::assertSentTo($activeAdmin, SubOutRequested::class);
    Notification::assertNotSentTo($inactiveAdmin, SubOutRequested::class);
});

test('it has urgent subject line', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create(['name' => 'Corporate Event']);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Medical emergency',
    ]);

    Notification::assertSentTo($admin, SubOutRequested::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_starts_with($mail->subject, 'URGENT:')
            && str_contains($mail->subject, 'Corporate Event');
    });
});

test('it includes gig name in notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create(['name' => 'Wedding Reception']);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Emergency',
    ]);

    Notification::assertSentTo($admin, SubOutRequested::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains(implode(' ', $mail->introLines), 'Wedding Reception');
    });
});

test('it includes gig date in notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create(['date' => '2025-07-20']);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Travel conflict',
    ]);

    Notification::assertSentTo($admin, SubOutRequested::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains(implode(' ', $mail->introLines), 'July 20, 2025');
    });
});

test('it includes musician name in notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create(['name' => 'Jane Smith']);
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Personal matter',
    ]);

    Notification::assertSentTo($admin, SubOutRequested::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains(implode(' ', $mail->introLines), 'Jane Smith');
    });
});

test('it includes instrument in notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create(['name' => 'Piano']);
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Health issue',
    ]);

    Notification::assertSentTo($admin, SubOutRequested::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains(implode(' ', $mail->introLines), 'Piano');
    });
});

test('it includes sub-out reason', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Unexpected family obligation I cannot avoid',
    ]);

    Notification::assertSentTo($admin, SubOutRequested::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains(implode(' ', $mail->introLines), 'Unexpected family obligation I cannot avoid');
    });
});

test('it includes link to admin panel', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
    ]);

    $this->actingAs($musician)->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Work conflict',
    ]);

    Notification::assertSentTo($admin, SubOutRequested::class, function ($notification) use ($gig) {
        $mail = $notification->toMail($notification->assignment->user);

        return $mail->actionText === 'View Gig in Admin'
            && str_contains($mail->actionUrl, '/admin/gigs/'.$gig->id);
    });
});

test('it queues the notification', function () {
    $notification = new SubOutRequested(
        GigAssignment::factory()->create()
    );

    expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});
