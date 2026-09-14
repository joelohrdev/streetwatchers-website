<?php

use App\Http\Controllers\ChapterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotoRemovalRequestController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

/*
 * Chapters are called "groups" on the public site, so their URLs live under /groups.
 * The route names, controller and database keep the internal "chapter" name.
 */
Route::get('groups', [ChapterController::class, 'index'])->name('chapters.index');

// Old /chapters links keep working, including any search in the query string.
Route::get('chapters/{path?}', fn (Request $request, ?string $path = null): RedirectResponse => redirect()->to(
    '/groups'.($path ? "/{$path}" : '').($request->getQueryString() ? '?'.$request->getQueryString() : ''),
    301,
))->where('path', '.*');

Route::get('photo-removal-requests/create', [PhotoRemovalRequestController::class, 'create'])->name('photo-removal-requests.create');
Route::post('photo-removal-requests', [PhotoRemovalRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('photo-removal-requests.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('groups/create', [ChapterController::class, 'create'])->name('chapters.create');
    Route::post('groups', [ChapterController::class, 'store'])->name('chapters.store');
});

// Registered after groups/create so that path isn't read as a group slug.
Route::get('groups/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
