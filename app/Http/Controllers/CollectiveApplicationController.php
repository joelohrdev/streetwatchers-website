<?php

namespace App\Http\Controllers;

use App\Enums\CollectiveApplicationStatus;
use App\Http\Requests\StoreCollectiveApplicationRequest;
use App\Models\Collective;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Applying to join a collective, and the founder's inbox of applications.
 */
class CollectiveApplicationController extends Controller
{
    /**
     * Applications to the collectives the signed-in user founded, pending first.
     */
    public function index(Request $request): Response
    {
        $collectives = $request->user()
            ->foundedCollectives()
            ->with(['applications' => fn ($query) => $query
                ->with('user')
                ->where(fn (Builder $query) => $query
                    ->where('status', CollectiveApplicationStatus::Pending)
                    ->orWhere('decided_at', '>=', now()->subDays(30)))
                ->latest('id')])
            ->orderBy('name')
            ->get()
            ->map(fn (Collective $collective): array => [
                'id' => $collective->id,
                'name' => $collective->name,
                'slug' => $collective->slug,
                'is_open_for_applications' => $collective->is_open_for_applications,
                'pending' => $this->pendingApplications($collective),
                'recent' => $this->recentDecisions($collective),
            ]);

        return Inertia::render('collectives/applications/Index', [
            'collectives' => $collectives,
        ]);
    }

    /**
     * Send a guest to log in or register, then bring them back to the collective page to apply.
     */
    public function create(Request $request, Collective $collective): RedirectResponse
    {
        $collectivePage = route('collectives.show', $collective);

        if ($request->user() !== null) {
            return redirect()->to($collectivePage);
        }

        redirect()->setIntendedUrl($collectivePage);

        return $request->query('via') === 'register'
            ? to_route('register')
            : to_route('login');
    }

    public function store(StoreCollectiveApplicationRequest $request, Collective $collective): RedirectResponse
    {
        $collective->applications()->create([
            'user_id' => $request->user()->id,
            'message' => $request->validated('message'),
            'status' => CollectiveApplicationStatus::Pending,
        ]);

        return to_route('collectives.show', $collective)->with('status', 'application-sent');
    }

    /**
     * @return list<array{id: int, applicant: string, message: string, created_at: string|null}>
     */
    private function pendingApplications(Collective $collective): array
    {
        $applications = [];

        foreach ($collective->applications as $application) {
            if ($application->status === CollectiveApplicationStatus::Pending) {
                $applications[] = [
                    'id' => $application->id,
                    'applicant' => $application->user->name,
                    'message' => $application->message,
                    'created_at' => $application->created_at?->toIso8601String(),
                ];
            }
        }

        return $applications;
    }

    /**
     * @return list<array{id: int, applicant: string, status: string, decided_at: string|null}>
     */
    private function recentDecisions(Collective $collective): array
    {
        $applications = [];

        foreach ($collective->applications as $application) {
            if ($application->status !== CollectiveApplicationStatus::Pending) {
                $applications[] = [
                    'id' => $application->id,
                    'applicant' => $application->user->name,
                    'status' => $application->status->value,
                    'decided_at' => $application->decided_at?->toIso8601String(),
                ];
            }
        }

        return $applications;
    }
}
