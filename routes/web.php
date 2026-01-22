<?php

use App\Http\Controllers\Admin\GigWorksheetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/gigs/{gig}/worksheet', [GigWorksheetController::class, 'show'])
        ->name('gigs.worksheet')
        ->can('view', 'gig');
});

require __DIR__.'/settings.php';
