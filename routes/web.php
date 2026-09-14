<?php

use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ChapterMembershipController;
use App\Http\Controllers\CollectiveApplicationController;
use App\Http\Controllers\CollectiveApplicationDecisionController;
use App\Http\Controllers\CollectiveController;
use App\Http\Controllers\CollectiveMembershipController;
use App\Http\Controllers\ContactMessageController;
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

Route::get('collectives', [CollectiveController::class, 'index'])->name('collectives.index');

// Old /chapters links keep working, including any search in the query string.
Route::get('chapters/{path?}', fn (Request $request, ?string $path = null): RedirectResponse => redirect()->to(
    '/groups'.($path ? "/{$path}" : '').($request->getQueryString() ? '?'.$request->getQueryString() : ''),
    301,
))->where('path', '.*');

Route::get('contact', [ContactMessageController::class, 'create'])->name('contact-messages.create');
Route::post('contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact-messages.store');

Route::get('photo-removal-requests/create', [PhotoRemovalRequestController::class, 'create'])->name('photo-removal-requests.create');
Route::post('photo-removal-requests', [PhotoRemovalRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('photo-removal-requests.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('groups/create', [ChapterController::class, 'create'])->name('chapters.create');
    Route::post('groups', [ChapterController::class, 'store'])->name('chapters.store');

    Route::post('groups/{chapter}/membership', [ChapterMembershipController::class, 'store'])->name('chapters.membership.store');
    Route::delete('groups/{chapter}/membership', [ChapterMembershipController::class, 'destroy'])->name('chapters.membership.destroy');

    Route::get('collectives/create', [CollectiveController::class, 'create'])->name('collectives.create');
    Route::post('collectives', [CollectiveController::class, 'store'])->name('collectives.store');
    Route::get('collectives/{collective}/edit', [CollectiveController::class, 'edit'])->name('collectives.edit');
    Route::put('collectives/{collective}', [CollectiveController::class, 'update'])->name('collectives.update');
    Route::post('collectives/{collective}/applications', [CollectiveApplicationController::class, 'store'])->name('collectives.applications.store');
    Route::delete('collectives/{collective}/membership', [CollectiveMembershipController::class, 'destroy'])->name('collectives.membership.destroy');

    Route::get('collective-applications', [CollectiveApplicationController::class, 'index'])->name('collective-applications.index');
    Route::patch('collective-applications/{collectiveApplication}', [CollectiveApplicationDecisionController::class, 'update'])->name('collective-applications.decision.update');
});

// Sends guests to log in or register and back again, so these deliberately have no auth middleware.
Route::get('groups/{chapter}/join', [ChapterMembershipController::class, 'create'])->name('chapters.membership.create');
Route::get('collectives/{collective}/apply', [CollectiveApplicationController::class, 'create'])->name('collectives.applications.create');

// Registered after collectives/create so that path isn't read as a collective slug.
Route::get('collectives/{collective}', [CollectiveController::class, 'show'])->name('collectives.show');

// Registered after groups/create so that path isn't read as a group slug.
Route::get('groups/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
