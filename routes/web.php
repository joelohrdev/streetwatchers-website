<?php

use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ChapterMemberController;
use App\Http\Controllers\ChapterMembershipController;
use App\Http\Controllers\ChapterOrganiserController;
use App\Http\Controllers\CollectiveApplicationController;
use App\Http\Controllers\CollectiveApplicationDecisionController;
use App\Http\Controllers\CollectiveController;
use App\Http\Controllers\CollectiveMembershipController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventCancellationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRsvpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotoRemovalRequestController;
use App\Http\Middleware\EnsureCollectivesAreEnabled;
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

Route::get('contact', [ContactMessageController::class, 'create'])->name('contact-messages.create');
Route::post('contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact-messages.store');

Route::get('photo-removal-requests/create', [PhotoRemovalRequestController::class, 'create'])->name('photo-removal-requests.create');
Route::post('photo-removal-requests', [PhotoRemovalRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('photo-removal-requests.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('groups/create', [ChapterController::class, 'create'])->name('chapters.create');
    Route::post('groups', [ChapterController::class, 'store'])->name('chapters.store');

    Route::post('groups/{chapter}/membership', [ChapterMembershipController::class, 'store'])->name('chapters.membership.store');
    Route::delete('groups/{chapter}/membership', [ChapterMembershipController::class, 'destroy'])->name('chapters.membership.destroy');

    Route::get('groups/{chapter}/members', [ChapterMemberController::class, 'index'])->name('chapters.members.index');
    Route::post('groups/{chapter}/organisers', [ChapterOrganiserController::class, 'store'])->name('chapters.organisers.store');
    Route::delete('groups/{chapter}/organisers', [ChapterOrganiserController::class, 'destroy'])->name('chapters.organisers.destroy');

    // Meetups are "events" internally, like groups are "chapters".
    Route::scopeBindings()->group(function () {
        Route::get('groups/{chapter}/meetups/create', [EventController::class, 'create'])->name('chapters.events.create');
        Route::post('groups/{chapter}/meetups', [EventController::class, 'store'])->name('chapters.events.store');
        Route::get('groups/{chapter}/meetups/{event}/edit', [EventController::class, 'edit'])->name('chapters.events.edit');
        Route::put('groups/{chapter}/meetups/{event}', [EventController::class, 'update'])->name('chapters.events.update');
        Route::post('groups/{chapter}/meetups/{event}/cancellation', [EventCancellationController::class, 'store'])->name('chapters.events.cancellation.store');
        Route::post('groups/{chapter}/meetups/{event}/rsvp', [EventRsvpController::class, 'store'])->name('chapters.events.rsvp.store');
        Route::delete('groups/{chapter}/meetups/{event}/rsvp', [EventRsvpController::class, 'destroy'])->name('chapters.events.rsvp.destroy');
    });
});

// Sends guests to log in or register and back again, so these deliberately have no auth middleware.
Route::get('groups/{chapter}/join', [ChapterMembershipController::class, 'create'])->name('chapters.membership.create');
Route::get('groups/{chapter}/meetups/{event}/rsvp', [EventRsvpController::class, 'create'])->scopeBindings()->name('chapters.events.rsvp.create');

// Registered after groups/create so that path isn't read as a group slug.
Route::get('groups/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');
Route::get('groups/{chapter}/meetups/{event}', [EventController::class, 'show'])->scopeBindings()->name('chapters.events.show');

// Collectives aren't part of the launch, so every public collective page is switched off by config('features.collectives').
Route::middleware(EnsureCollectivesAreEnabled::class)->group(function () {
    Route::get('collectives', [CollectiveController::class, 'index'])->name('collectives.index');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('collectives/create', [CollectiveController::class, 'create'])->name('collectives.create');
        Route::post('collectives', [CollectiveController::class, 'store'])->name('collectives.store');
        Route::get('collectives/{collective}/edit', [CollectiveController::class, 'edit'])->name('collectives.edit');
        Route::put('collectives/{collective}', [CollectiveController::class, 'update'])->name('collectives.update');
        Route::post('collectives/{collective}/applications', [CollectiveApplicationController::class, 'store'])->name('collectives.applications.store');
        Route::delete('collectives/{collective}/membership', [CollectiveMembershipController::class, 'destroy'])->name('collectives.membership.destroy');

        Route::get('collective-applications', [CollectiveApplicationController::class, 'index'])->name('collective-applications.index');
        Route::patch('collective-applications/{collectiveApplication}', [CollectiveApplicationDecisionController::class, 'update'])->name('collective-applications.decision.update');
    });

    // Sends guests to log in or register and back again, so this deliberately has no auth middleware.
    Route::get('collectives/{collective}/apply', [CollectiveApplicationController::class, 'create'])->name('collectives.applications.create');

    // Registered after collectives/create so that path isn't read as a collective slug.
    Route::get('collectives/{collective}', [CollectiveController::class, 'show'])->name('collectives.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
