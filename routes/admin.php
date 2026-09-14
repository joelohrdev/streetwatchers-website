<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\ArticlePublicationController;
use App\Http\Controllers\Admin\ChapterAdminController;
use App\Http\Controllers\Admin\ChapterApprovalController;
use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\ChapterDeactivationController;
use App\Http\Controllers\Admin\CollectiveController;
use App\Http\Controllers\Admin\CollectiveVerificationController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CorrespondentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReportPhotoStatusController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TagMergeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserStatusController;
use Illuminate\Support\Facades\Route;

/*
 * Every route in this group is restricted to super admins by the access-admin gate.
 * Add new admin routes inside the group so tests/Feature/Admin/AdminAccessTest.php covers them.
 */
Route::middleware(['auth', 'verified', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('chapters', [ChapterController::class, 'index'])->name('chapters.index');
        Route::get('chapters/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');
        Route::post('chapters/{chapter}/approval', [ChapterApprovalController::class, 'store'])->name('chapters.approval.store');
        Route::post('chapters/{chapter}/deactivation', [ChapterDeactivationController::class, 'store'])->name('chapters.deactivation.store');
        Route::post('chapters/{chapter}/admins', [ChapterAdminController::class, 'store'])->name('chapters.admins.store');
        Route::delete('chapters/{chapter}/admins/{user}', [ChapterAdminController::class, 'destroy'])->name('chapters.admins.destroy');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::patch('reports/{report}', [ReportController::class, 'update'])->name('reports.update');
        Route::put('reports/{report}/photo-status', [ReportPhotoStatusController::class, 'update'])->name('reports.photo-status.update');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::put('users/{user}/status', [UserStatusController::class, 'update'])->name('users.status.update');

        Route::get('collectives', [CollectiveController::class, 'index'])->name('collectives.index');
        Route::delete('collectives/{collective}', [CollectiveController::class, 'destroy'])->name('collectives.destroy');
        Route::post('collectives/{collective}/verification', [CollectiveVerificationController::class, 'store'])->name('collectives.verification.store');
        Route::delete('collectives/{collective}/verification', [CollectiveVerificationController::class, 'destroy'])->name('collectives.verification.destroy');

        Route::get('correspondents', [CorrespondentController::class, 'index'])->name('correspondents.index');
        Route::post('correspondents', [CorrespondentController::class, 'store'])->name('correspondents.store');
        Route::delete('correspondents/{correspondent}', [CorrespondentController::class, 'destroy'])->name('correspondents.destroy');

        Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
        Route::delete('articles/{article}/publication', [ArticlePublicationController::class, 'destroy'])->name('articles.publication.destroy');

        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
        Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
        Route::post('tags/{tag}/merge', [TagMergeController::class, 'store'])->name('tags.merge.store');

        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
