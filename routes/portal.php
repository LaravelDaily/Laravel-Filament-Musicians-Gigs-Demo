<?php

use App\Http\Controllers\Portal\PortalDashboardController;
use App\Models\Gig;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'musician'])->prefix('portal')->name('portal.')->group(function (): void {
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');

    // Placeholder routes for navigation (to be implemented in Phase 7.7 and 7.8)
    Route::view('/gigs/past', 'portal.past-gigs')->name('gigs.past');
    Route::view('/profile', 'portal.profile')->name('profile');

    // Placeholder route for gig detail (to be implemented in Phase 7.3) - must be after /gigs/past
    Route::get('/gigs/{gig}', fn (Gig $gig) => view('portal.gig-detail', ['gig' => $gig]))->name('gigs.show');
});
