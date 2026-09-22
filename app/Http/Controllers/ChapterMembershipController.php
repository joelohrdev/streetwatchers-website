<?php

namespace App\Http\Controllers;

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Models\Chapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Joining and leaving a group. Chapters are called "groups" on the public site.
 */
class ChapterMembershipController extends Controller
{
    /**
     * Send a guest to log in or register, then bring them back to the group page to join.
     */
    public function create(Request $request, Chapter $chapter): RedirectResponse
    {
        $this->ensureActive($chapter);

        $groupPage = route('chapters.show', $chapter);

        if ($request->user() !== null) {
            return redirect()->to($groupPage);
        }

        redirect()->setIntendedUrl($groupPage);

        return $request->query('via') === 'register'
            ? to_route('register')
            : to_route('login');
    }

    public function store(Request $request, Chapter $chapter): RedirectResponse
    {
        $this->ensureActive($chapter);

        // Attach only when the user isn't already in the group, so an organiser keeps their admin role.
        if (! $chapter->members()->whereKey($request->user()->id)->exists()) {
            $chapter->members()->attach($request->user(), ['role' => ChapterMemberRole::Member]);
        }

        return to_route('chapters.show', $chapter)->with('status', 'group-joined');
    }

    public function destroy(Request $request, Chapter $chapter): RedirectResponse
    {
        $membership = $chapter->members()->whereKey($request->user()->id)->first()?->pivot;

        if ($membership?->role === ChapterMemberRole::Admin) {
            throw ValidationException::withMessages([
                'membership' => 'Step down as an organiser from the members page before you leave the group.',
            ]);
        }

        $chapter->members()->detach($request->user());

        return to_route('chapters.show', $chapter)->with('status', 'group-left');
    }

    private function ensureActive(Chapter $chapter): void
    {
        abort_unless($chapter->status === ChapterStatus::Active, 404);
    }
}
