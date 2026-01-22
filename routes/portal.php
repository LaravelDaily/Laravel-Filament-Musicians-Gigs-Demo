<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'musician'])->prefix('portal')->name('portal.')->group(function (): void {
    Route::view('/', 'portal.dashboard')->name('dashboard');
});
