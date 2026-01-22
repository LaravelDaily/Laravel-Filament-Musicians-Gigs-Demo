<?php

use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalGigController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'musician'])->prefix('portal')->name('portal.')->group(function (): void {
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');

    // Placeholder routes for navigation (to be implemented in Phase 7.7 and 7.8)
    Route::view('/gigs/past', 'portal.past-gigs')->name('gigs.past');
    Route::view('/profile', 'portal.profile')->name('profile');

    // Gig routes (must be after /gigs/past to avoid route conflicts)
    Route::get('/gigs/{gig}', [PortalGigController::class, 'show'])->name('gigs.show');
    Route::post('/gigs/{gig}/accept', [PortalGigController::class, 'accept'])->name('gigs.accept');
    Route::post('/gigs/{gig}/decline', [PortalGigController::class, 'decline'])->name('gigs.decline');
});
