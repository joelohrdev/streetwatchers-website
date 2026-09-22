<?php

namespace App\Http\Controllers;

use App\Enums\ChapterStatus;
use App\Models\Chapter;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

/**
 * Short links for sharing, such as /g/1B for a group and /m/4k for a meetup, which redirect to the full page.
 */
class ShortLinkController extends Controller
{
    public function group(string $code): RedirectResponse
    {
        $chapter = Chapter::findByShortCode($code);

        abort_unless($chapter?->status === ChapterStatus::Active, 404);

        return redirect()->route('chapters.show', $chapter, 301);
    }

    public function meetup(string $code): RedirectResponse
    {
        $event = Event::findByShortCode($code)?->load('chapter');

        abort_unless($event?->chapter->status === ChapterStatus::Active, 404);

        return redirect()->route('chapters.events.show', [$event->chapter, $event], 301);
    }
}
