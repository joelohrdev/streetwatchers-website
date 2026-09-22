<?php

namespace App\Http\Middleware;

use App\Enums\CollectiveApplicationStatus;
use App\Models\CollectiveApplication;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'announcement' => fn (): ?string => Setting::announcementBanner(),
            'unreadContactMessages' => fn (): ?int => $request->user()?->isSuperAdmin()
                ? ContactMessage::query()->whereNull('read_at')->count()
                : null,
            'analytics' => config('services.google_analytics.measurement_id')
                ? ['measurementId' => config('services.google_analytics.measurement_id')]
                : null,
            'features' => [
                'collectives' => (bool) config('features.collectives'),
            ],
            'pendingCollectiveApplications' => fn (): ?int => config('features.collectives')
                ? $this->pendingCollectiveApplications($request)
                : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * Pending applications to collectives the user founded, or null when they founded none.
     */
    private function pendingCollectiveApplications(Request $request): ?int
    {
        $user = $request->user();

        if ($user === null || ! $user->foundedCollectives()->exists()) {
            return null;
        }

        return CollectiveApplication::query()
            ->where('status', CollectiveApplicationStatus::Pending)
            ->whereIn('collective_id', $user->foundedCollectives()->select('collectives.id'))
            ->count();
    }
}
