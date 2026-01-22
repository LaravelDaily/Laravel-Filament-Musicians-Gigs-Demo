<?php

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Filament\Widgets\ActiveMusiciansWidget;
use App\Filament\Widgets\NeedsAttentionWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\UpcomingGigsWidget;
use App\Models\AssignmentStatusLog;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it shows upcoming gigs count widget', function () {
    Livewire::test(UpcomingGigsWidget::class)
        ->assertSuccessful()
        ->assertSee('Upcoming Gigs');
});

test('it shows correct count for next 7 days', function () {
    // Create gigs within next 7 days (should be counted)
    Gig::factory()->count(3)->create([
        'status' => GigStatus::Active,
        'date' => now()->addDays(3),
    ]);

    // Create gig outside the 7-day window (should not be counted)
    Gig::factory()->create([
        'status' => GigStatus::Active,
        'date' => now()->addDays(10),
    ]);

    // Create draft gig within 7 days (should not be counted)
    Gig::factory()->create([
        'status' => GigStatus::Draft,
        'date' => now()->addDays(2),
    ]);

    // Create past gig (should not be counted)
    Gig::factory()->create([
        'status' => GigStatus::Active,
        'date' => now()->subDays(2),
    ]);

    Livewire::test(UpcomingGigsWidget::class)
        ->assertSee('3');
});

test('it shows needs attention widget', function () {
    Livewire::test(NeedsAttentionWidget::class)
        ->assertSuccessful()
        ->assertSee('Pending Responses')
        ->assertSee('Sub-out Requests');
});

test('it counts gigs with pending responses', function () {
    // Create gigs with pending assignments
    $gig1 = Gig::factory()->create([
        'status' => GigStatus::Active,
        'date' => now()->addDays(5),
    ]);
    GigAssignment::factory()->create([
        'gig_id' => $gig1->id,
        'status' => AssignmentStatus::Pending,
    ]);

    $gig2 = Gig::factory()->create([
        'status' => GigStatus::Active,
        'date' => now()->addDays(3),
    ]);
    GigAssignment::factory()->create([
        'gig_id' => $gig2->id,
        'status' => AssignmentStatus::Pending,
    ]);

    // Create gig with accepted assignment (should not be counted)
    $gig3 = Gig::factory()->create([
        'status' => GigStatus::Active,
        'date' => now()->addDays(4),
    ]);
    GigAssignment::factory()->create([
        'gig_id' => $gig3->id,
        'status' => AssignmentStatus::Accepted,
    ]);

    // Create past gig with pending (should not be counted)
    $gig4 = Gig::factory()->create([
        'status' => GigStatus::Active,
        'date' => now()->subDays(2),
    ]);
    GigAssignment::factory()->create([
        'gig_id' => $gig4->id,
        'status' => AssignmentStatus::Pending,
    ]);

    Livewire::test(NeedsAttentionWidget::class)
        ->assertSeeInOrder(['Pending Responses', '2']);
});

test('it counts gigs with sub-out requests', function () {
    // Create gigs with sub-out requests
    $gig1 = Gig::factory()->create([
        'status' => GigStatus::Active,
        'date' => now()->addDays(5),
    ]);
    GigAssignment::factory()->create([
        'gig_id' => $gig1->id,
        'status' => AssignmentStatus::SuboutRequested,
    ]);

    $gig2 = Gig::factory()->create([
        'status' => GigStatus::Active,
        'date' => now()->addDays(3),
    ]);
    GigAssignment::factory()->create([
        'gig_id' => $gig2->id,
        'status' => AssignmentStatus::SuboutRequested,
    ]);

    // Create gig with accepted assignment (should not be counted)
    $gig3 = Gig::factory()->create([
        'status' => GigStatus::Active,
        'date' => now()->addDays(4),
    ]);
    GigAssignment::factory()->create([
        'gig_id' => $gig3->id,
        'status' => AssignmentStatus::Accepted,
    ]);

    Livewire::test(NeedsAttentionWidget::class)
        ->assertSeeInOrder(['Sub-out Requests', '2']);
});

test('it shows recent activity widget', function () {
    Livewire::test(RecentActivityWidget::class)
        ->assertSuccessful()
        ->assertSee('Recent Declines')
        ->assertSee('Recent Sub-outs');
});

test('it shows declines from last 24 hours', function () {
    $assignment1 = GigAssignment::factory()->create();
    $assignment2 = GigAssignment::factory()->create();
    $assignment3 = GigAssignment::factory()->create();

    // Create declines within 24 hours
    AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment1->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Declined->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now()->subHours(2),
    ]);

    AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment2->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Declined->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now()->subHours(12),
    ]);

    // Create decline outside 24 hours (should not be counted)
    AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment3->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Declined->value,
        'reason' => null,
        'changed_by_user_id' => null,
        'created_at' => now()->subDays(2),
    ]);

    Livewire::test(RecentActivityWidget::class)
        ->assertSeeInOrder(['Recent Declines', '2']);
});

test('it shows sub-outs from last 24 hours', function () {
    $assignment1 = GigAssignment::factory()->create();
    $assignment2 = GigAssignment::factory()->create();

    // Create sub-out within 24 hours
    AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment1->id,
        'old_status' => AssignmentStatus::Accepted->value,
        'new_status' => AssignmentStatus::SuboutRequested->value,
        'reason' => 'Test reason',
        'changed_by_user_id' => null,
        'created_at' => now()->subHours(5),
    ]);

    // Create sub-out outside 24 hours (should not be counted)
    AssignmentStatusLog::create([
        'gig_assignment_id' => $assignment2->id,
        'old_status' => AssignmentStatus::Accepted->value,
        'new_status' => AssignmentStatus::SuboutRequested->value,
        'reason' => 'Test reason',
        'changed_by_user_id' => null,
        'created_at' => now()->subDays(3),
    ]);

    Livewire::test(RecentActivityWidget::class)
        ->assertSeeInOrder(['Recent Sub-outs', '1']);
});

test('it shows active musicians count widget', function () {
    Livewire::test(ActiveMusiciansWidget::class)
        ->assertSuccessful()
        ->assertSee('Active Musicians');
});

test('it shows correct active musicians count', function () {
    // Create active musicians
    User::factory()->count(5)->musician()->create(['is_active' => true]);

    // Create inactive musicians (should not be counted)
    User::factory()->count(2)->musician()->inactive()->create();

    // Create active admin (should not be counted)
    User::factory()->admin()->create(['is_active' => true]);

    Livewire::test(ActiveMusiciansWidget::class)
        ->assertSee('5');
});
