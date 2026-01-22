<?php

use App\Enums\AssignmentStatus;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Models\AssignmentStatusLog;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render audit log page', function () {
    $this->get(AuditLogResource::getUrl('index'))
        ->assertSuccessful();
});

test('it displays status changes', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
    ]);

    $log = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'reason' => null,
        'changed_by_user_id' => $musician->id,
        'created_at' => now(),
    ]);

    Livewire::test(ListAuditLogs::class)
        ->assertCanSeeTableRecords([$log]);
});

test('it shows timestamps', function () {
    $assignment = GigAssignment::factory()->create();
    $timestamp = now()->subDay();

    $log = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => $timestamp,
    ]);

    Livewire::test(ListAuditLogs::class)
        ->assertSee($timestamp->format('M j, Y'));
});

test('it shows gig name', function () {
    $gig = Gig::factory()->create(['name' => 'Jazz Night Concert']);
    $assignment = GigAssignment::factory()->create(['gig_id' => $gig->id]);

    $log = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Declined->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    Livewire::test(ListAuditLogs::class)
        ->assertSee('Jazz Night Concert');
});

test('it shows musician name', function () {
    $musician = User::factory()->musician()->create(['name' => 'John Doe Musician']);
    $assignment = GigAssignment::factory()->create(['user_id' => $musician->id]);

    $log = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    Livewire::test(ListAuditLogs::class)
        ->assertSee('John Doe Musician');
});

test('it shows status transition', function () {
    $assignment = GigAssignment::factory()->create();

    $log = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    Livewire::test(ListAuditLogs::class)
        ->assertSee(AssignmentStatus::Pending->getLabel())
        ->assertSee(AssignmentStatus::Accepted->getLabel());
});

test('it shows reason when provided', function () {
    $assignment = GigAssignment::factory()->create();

    $log = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Accepted->value,
        'new_status' => AssignmentStatus::SuboutRequested->value,
        'reason' => 'Family emergency',
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    Livewire::test(ListAuditLogs::class)
        ->assertSee('Family emergency');
});

test('it can filter by date range', function () {
    $assignment = GigAssignment::factory()->create();

    $oldLog = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now()->subDays(10),
    ]);

    $recentLog = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Accepted->value,
        'new_status' => AssignmentStatus::SuboutRequested->value,
        'reason' => 'Test reason',
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    Livewire::test(ListAuditLogs::class)
        ->filterTable('created_at', [
            'from' => now()->subDays(5)->format('Y-m-d'),
            'until' => now()->addDay()->format('Y-m-d'),
        ])
        ->assertCanSeeTableRecords([$recentLog])
        ->assertCanNotSeeTableRecords([$oldLog]);
});

test('it can filter by gig', function () {
    $gig1 = Gig::factory()->create(['name' => 'Gig One']);
    $gig2 = Gig::factory()->create(['name' => 'Gig Two']);

    $assignment1 = GigAssignment::factory()->create(['gig_id' => $gig1->id]);
    $assignment2 = GigAssignment::factory()->create(['gig_id' => $gig2->id]);

    $log1 = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment1->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    $log2 = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment2->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Declined->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    Livewire::test(ListAuditLogs::class)
        ->filterTable('gig', $gig1->id)
        ->assertCanSeeTableRecords([$log1])
        ->assertCanNotSeeTableRecords([$log2]);
});

test('it can filter by musician', function () {
    $musician1 = User::factory()->musician()->create(['name' => 'Musician One']);
    $musician2 = User::factory()->musician()->create(['name' => 'Musician Two']);

    $assignment1 = GigAssignment::factory()->create(['user_id' => $musician1->id]);
    $assignment2 = GigAssignment::factory()->create(['user_id' => $musician2->id]);

    $log1 = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment1->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    $log2 = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment2->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Declined->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    Livewire::test(ListAuditLogs::class)
        ->filterTable('musician', $musician1->id)
        ->assertCanSeeTableRecords([$log1])
        ->assertCanNotSeeTableRecords([$log2]);
});

test('it sorts by timestamp descending', function () {
    $assignment = GigAssignment::factory()->create();

    $oldLog = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now()->subDay(),
    ]);

    $recentLog = AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Accepted->value,
        'new_status' => AssignmentStatus::SuboutRequested->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now(),
    ]);

    Livewire::test(ListAuditLogs::class)
        ->assertCanSeeTableRecords([$recentLog, $oldLog], inOrder: true);
});
