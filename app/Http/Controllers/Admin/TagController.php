<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $tags = Tag::query()
            ->when($search !== '', fn (Builder $query) => $query->whereLike('name', "%{$search}%"))
            ->withCount('photos')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString()
            ->through(fn (Tag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'photos_count' => $tag->photos_count,
            ]);

        return Inertia::render('admin/tags/Index', [
            'tags' => $tags,
            'allTags' => Tag::query()->orderBy('name')->get(['id', 'name']),
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Delete a tag that no photos use. Tags in use must be merged instead.
     */
    public function destroy(Request $request, Tag $tag): RedirectResponse
    {
        if ($tag->photos()->exists()) {
            throw ValidationException::withMessages([
                'tag' => 'This tag is still used by photos. Merge it into another tag instead.',
            ]);
        }

        DB::transaction(function () use ($request, $tag): void {
            $tag->delete();

            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::TagDeleted,
                subject: $tag,
                metadata: ['tag_name' => $tag->name],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tag ":name" deleted.', ['name' => $tag->name])]);

        return back();
    }
}
