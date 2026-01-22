<?php

use App\Enums\AssignmentStatus;
use App\Models\AssignmentStatusLog;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;
use Illuminate\Database\QueryException;

it('has gig relationship', function () {
    $gig = Gig::factory()->create();
    $assignment = GigAssignment::factory()->create(['gig_id' => $gig->id]);

    expect($assignment->gig->id)->toBe($gig->id);
});

it('has user relationship', function () {
    $user = User::factory()->create();
    $assignment = GigAssignment::factory()->create(['user_id' => $user->id]);

    expect($assignment->user->id)->toBe($user->id);
});

it('has instrument relationship', function () {
    $instrument = Instrument::factory()->create();
    $assignment = GigAssignment::factory()->create(['instrument_id' => $instrument->id]);

    expect($assignment->instrument->id)->toBe($instrument->id);
});

it('has statusLogs relationship', function () {
    $assignment = GigAssignment::factory()->create();
    $log = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'created_at' => now(),
    ]);

    expect($assignment->statusLogs)->toHaveCount(1);
    expect($assignment->statusLogs->first()->id)->toBe($log->id);
});

it('casts status to AssignmentStatus enum', function () {
    $assignment = GigAssignment::factory()->accepted()->create();

    expect($assignment->status)->toBeInstanceOf(AssignmentStatus::class);
    expect($assignment->status)->toBe(AssignmentStatus::Accepted);
});

it('has unique constraint on gig_id and user_id', function () {
    $gig = Gig::factory()->create();
    $user = User::factory()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $user->id,
        'instrument_id' => $instrument->id,
    ]);

    expect(fn () => GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $user->id,
        'instrument_id' => $instrument->id,
    ]))->toThrow(QueryException::class);
});
