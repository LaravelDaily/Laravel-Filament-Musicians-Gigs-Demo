<?php

use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalGigController;
use App\Http\Controllers\Portal\PortalProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'musician'])->prefix('portal')->name('portal.')->group(function (): void {
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');

    // Past gigs route (must be before /gigs/{gig} to avoid route conflicts)
    Route::get('/gigs/past', [PortalGigController::class, 'past'])->name('gigs.past');
    // Profile route
    Route::get('/profile', [PortalProfileController::class, 'show'])->name('profile');

    // Gig routes (must be after /gigs/past to avoid route conflicts)
    Route::get('/gigs/{gig}', [PortalGigController::class, 'show'])->name('gigs.show');
    Route::post('/gigs/{gig}/accept', [PortalGigController::class, 'accept'])->name('gigs.accept');
    Route::post('/gigs/{gig}/decline', [PortalGigController::class, 'decline'])->name('gigs.decline');
    Route::post('/gigs/{gig}/subout', [PortalGigController::class, 'subout'])->name('gigs.subout');
});
