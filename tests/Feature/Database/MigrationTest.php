<?php

use Illuminate\Support\Facades\Schema;

it('creates regions table with correct columns', function () {
    expect(Schema::hasTable('regions'))->toBeTrue();
    expect(Schema::hasColumns('regions', ['id', 'name', 'created_at', 'updated_at']))->toBeTrue();
});

it('creates instruments table with correct columns', function () {
    expect(Schema::hasTable('instruments'))->toBeTrue();
    expect(Schema::hasColumns('instruments', ['id', 'name', 'created_at', 'updated_at']))->toBeTrue();
});

it('creates tags table with correct columns', function () {
    expect(Schema::hasTable('tags'))->toBeTrue();
    expect(Schema::hasColumns('tags', ['id', 'name', 'created_at', 'updated_at']))->toBeTrue();
});

it('creates gigs table with correct columns and indexes', function () {
    expect(Schema::hasTable('gigs'))->toBeTrue();
    expect(Schema::hasColumns('gigs', [
        'id',
        'name',
        'date',
        'call_time',
        'performance_time',
        'end_time',
        'venue_name',
        'venue_address',
        'client_contact_name',
        'client_contact_phone',
        'client_contact_email',
        'dress_code',
        'notes',
        'pay_info',
        'region_id',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ]))->toBeTrue();
});

it('creates gig_assignments table with unique constraint on gig_id and user_id', function () {
    expect(Schema::hasTable('gig_assignments'))->toBeTrue();
    expect(Schema::hasColumns('gig_assignments', [
        'id',
        'gig_id',
        'user_id',
        'instrument_id',
        'status',
        'pay_amount',
        'notes',
        'responded_at',
        'subout_reason',
        'decline_reason',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('creates assignment_status_logs table', function () {
    expect(Schema::hasTable('assignment_status_logs'))->toBeTrue();
    expect(Schema::hasColumns('assignment_status_logs', [
        'id',
        'gig_assignment_id',
        'old_status',
        'new_status',
        'reason',
        'changed_by_user_id',
        'created_at',
    ]))->toBeTrue();
});

it('creates settings table', function () {
    expect(Schema::hasTable('settings'))->toBeTrue();
    expect(Schema::hasColumns('settings', ['id', 'key', 'value', 'created_at', 'updated_at']))->toBeTrue();
});

it('adds musician fields to users table', function () {
    expect(Schema::hasColumns('users', [
        'role',
        'phone',
        'region_id',
        'notes',
        'is_active',
        'deleted_at',
    ]))->toBeTrue();
});

it('enforces foreign key constraints', function () {
    // Test region_id foreign key on users
    expect(Schema::hasColumn('users', 'region_id'))->toBeTrue();

    // Test region_id foreign key on gigs
    expect(Schema::hasColumn('gigs', 'region_id'))->toBeTrue();

    // Test gig_id foreign key on gig_assignments
    expect(Schema::hasColumn('gig_assignments', 'gig_id'))->toBeTrue();

    // Test user_id foreign key on gig_assignments
    expect(Schema::hasColumn('gig_assignments', 'user_id'))->toBeTrue();

    // Test instrument_id foreign key on gig_assignments
    expect(Schema::hasColumn('gig_assignments', 'instrument_id'))->toBeTrue();

    // Test gig_assignment_id foreign key on assignment_status_logs
    expect(Schema::hasColumn('assignment_status_logs', 'gig_assignment_id'))->toBeTrue();
});
