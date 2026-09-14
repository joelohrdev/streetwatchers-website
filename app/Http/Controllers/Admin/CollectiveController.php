<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Collective;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CollectiveController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $collectives = Collective::query()
            ->when($search !== '', fn (Builder $query) => $query->whereLike('name', "%{$search}%"))
            ->withCount('members')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Collective $collective): array => [
                'id' => $collective->id,
                'name' => $collective->name,
                'slug' => $collective->slug,
                'website_url' => $collective->website_url,
                'is_verified' => $collective->is_verified,
                'is_open_for_applications' => $collective->is_open_for_applications,
                'members_count' => $collective->members_count,
                'created_at' => $collective->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/collectives/Index', [
            'collectives' => $collectives,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Soft delete the collective so it disappears from the platform but can be restored.
     */
    public function destroy(Request $request, Collective $collective): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $collective, $validated): void {
            $collective->delete();

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::CollectiveRemoved,
                subject: $collective,
                reason: $validated['reason'],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name removed.', ['name' => $collective->name])]);

        return back();
    }
}
