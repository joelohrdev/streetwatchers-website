<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Http\Requests\StorePhotoRemovalRequest;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The public, no-login form for asking that a photo be taken down.
 */
class PhotoRemovalRequestController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('photo-removal-requests/Create', [
            'submitted' => $request->session()->get('status') === 'photo-removal-request-submitted',
        ]);
    }

    public function store(StorePhotoRemovalRequest $request): RedirectResponse
    {
        Report::query()->create([
            'reportable_type' => $request->photo()->getMorphClass(),
            'reportable_id' => $request->photo()->id,
            'reporter_id' => null,
            'reporter_email' => $request->validated('email'),
            'is_public_submission' => true,
            'reason' => $request->validated('reason'),
            'status' => ReportStatus::Open,
        ]);

        return to_route('photo-removal-requests.create')->with('status', 'photo-removal-request-submitted');
    }
}
