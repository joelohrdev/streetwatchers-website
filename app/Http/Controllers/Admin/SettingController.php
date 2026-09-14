<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\SettingKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('admin/settings/Edit', [
            'settings' => [
                'new_photos_require_review' => Setting::newPhotosRequireReview(),
                'announcement_banner' => Setting::announcementBanner(),
            ],
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $old = [
            'new_photos_require_review' => Setting::newPhotosRequireReview(),
            'announcement_banner' => Setting::announcementBanner(),
        ];

        $new = [
            'new_photos_require_review' => $request->boolean('new_photos_require_review'),
            'announcement_banner' => trim((string) $request->validated('announcement_banner')) ?: null,
        ];

        $changes = [];

        foreach ($new as $key => $value) {
            if ($old[$key] !== $value) {
                $changes[$key] = ['from' => $old[$key], 'to' => $value];
            }
        }

        if ($changes !== []) {
            DB::transaction(function () use ($request, $new, $changes): void {
                Setting::store(SettingKey::NewPhotosRequireReview, $new['new_photos_require_review'] ? '1' : '0');
                Setting::store(SettingKey::AnnouncementBanner, $new['announcement_banner']);

                AuditLog::record(
                    actor: $request->user(),
                    action: AuditAction::SettingsUpdated,
                    metadata: $changes,
                );
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Settings saved.')]);

        return back();
    }
}
