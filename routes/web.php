<?php

use App\Http\Controllers\PhotoRemovalRequestController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Home')->name('home');

Route::get('photo-removal-requests/create', [PhotoRemovalRequestController::class, 'create'])->name('photo-removal-requests.create');
Route::post('photo-removal-requests', [PhotoRemovalRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('photo-removal-requests.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
