<?php

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Enums\UserRole;

describe('UserRole enum', function () {
    it('has correct values for UserRole enum', function () {
        expect(UserRole::Admin->value)->toBe('admin');
        expect(UserRole::Musician->value)->toBe('musician');
        expect(UserRole::cases())->toHaveCount(2);
    });

    it('returns correct labels for UserRole enum', function () {
        expect(UserRole::Admin->getLabel())->toBe('Admin');
        expect(UserRole::Musician->getLabel())->toBe('Musician');
    });

    it('returns correct colors for UserRole enum', function () {
        expect(UserRole::Admin->getColor())->toBe('danger');
        expect(UserRole::Musician->getColor())->toBe('primary');
    });

    it('returns correct icons for UserRole enum', function () {
        expect(UserRole::Admin->getIcon())->toBe('heroicon-o-shield-check');
        expect(UserRole::Musician->getIcon())->toBe('heroicon-o-musical-note');
    });
});

describe('GigStatus enum', function () {
    it('has correct values for GigStatus enum', function () {
        expect(GigStatus::Draft->value)->toBe('draft');
        expect(GigStatus::Active->value)->toBe('active');
        expect(GigStatus::Cancelled->value)->toBe('cancelled');
        expect(GigStatus::cases())->toHaveCount(3);
    });

    it('returns correct labels for GigStatus enum', function () {
        expect(GigStatus::Draft->getLabel())->toBe('Draft');
        expect(GigStatus::Active->getLabel())->toBe('Active');
        expect(GigStatus::Cancelled->getLabel())->toBe('Cancelled');
    });

    it('returns correct colors for GigStatus enum', function () {
        expect(GigStatus::Draft->getColor())->toBe('gray');
        expect(GigStatus::Active->getColor())->toBe('success');
        expect(GigStatus::Cancelled->getColor())->toBe('danger');
    });

    it('returns correct icons for GigStatus enum', function () {
        expect(GigStatus::Draft->getIcon())->toBe('heroicon-o-pencil-square');
        expect(GigStatus::Active->getIcon())->toBe('heroicon-o-check-circle');
        expect(GigStatus::Cancelled->getIcon())->toBe('heroicon-o-x-circle');
    });
});

describe('AssignmentStatus enum', function () {
    it('has correct values for AssignmentStatus enum', function () {
        expect(AssignmentStatus::Pending->value)->toBe('pending');
        expect(AssignmentStatus::Accepted->value)->toBe('accepted');
        expect(AssignmentStatus::Declined->value)->toBe('declined');
        expect(AssignmentStatus::SuboutRequested->value)->toBe('subout_requested');
        expect(AssignmentStatus::cases())->toHaveCount(4);
    });

    it('returns correct labels for AssignmentStatus enum', function () {
        expect(AssignmentStatus::Pending->getLabel())->toBe('Pending');
        expect(AssignmentStatus::Accepted->getLabel())->toBe('Accepted');
        expect(AssignmentStatus::Declined->getLabel())->toBe('Declined');
        expect(AssignmentStatus::SuboutRequested->getLabel())->toBe('Sub-out Requested');
    });

    it('returns correct colors for AssignmentStatus enum', function () {
        expect(AssignmentStatus::Pending->getColor())->toBe('warning');
        expect(AssignmentStatus::Accepted->getColor())->toBe('success');
        expect(AssignmentStatus::Declined->getColor())->toBe('danger');
        expect(AssignmentStatus::SuboutRequested->getColor())->toBe('info');
    });

    it('returns correct icons for AssignmentStatus enum', function () {
        expect(AssignmentStatus::Pending->getIcon())->toBe('heroicon-o-clock');
        expect(AssignmentStatus::Accepted->getIcon())->toBe('heroicon-o-check');
        expect(AssignmentStatus::Declined->getIcon())->toBe('heroicon-o-x-mark');
        expect(AssignmentStatus::SuboutRequested->getIcon())->toBe('heroicon-o-arrow-path');
    });
});
