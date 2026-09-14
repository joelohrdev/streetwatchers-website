<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MergeTagRequest;
use App\Models\AuditLog;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TagMergeController extends Controller
{
    /**
     * Move every photo from the source tag onto the target tag, then delete the source tag.
     */
    public function store(MergeTagRequest $request, Tag $tag): RedirectResponse
    {
        $target = $request->targetTag();

        DB::transaction(function () use ($request, $tag, $target): void {
            $photoIds = $tag->photos()->pluck('photos.id');

            // Photos already tagged with both keep a single photo_tag row for the target.
            $target->photos()->syncWithoutDetaching($photoIds);
            $tag->delete();

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::TagMerged,
                subject: $target,
                metadata: [
                    'source_tag_id' => $tag->id,
                    'source_tag_name' => $tag->name,
                    'photo_count' => $photoIds->count(),
                ],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Merged ":source" into ":target".', [
            'source' => $tag->name,
            'target' => $target->name,
        ])]);

        return back();
    }
}
