<?php

namespace App\Http\Controllers;

use App\Enums\ChapterStatus;
use App\Models\Chapter;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

/**
 * The XML sitemap search engines read, built from the live data so new groups and meetups appear straight away.
 * public/robots.txt points search engines to it.
 */
class SitemapController extends Controller
{
    /**
     * Every public page worth indexing: the fixed pages, each active group, and meetups that haven't finished.
     * Collectives are left out while they're switched off.
     */
    public function sitemap(): Response
    {
        $pages = collect([
            ['loc' => route('home'), 'lastmod' => null],
            ['loc' => route('chapters.index'), 'lastmod' => null],
            ['loc' => route('code-of-conduct'), 'lastmod' => null],
            ['loc' => route('privacy'), 'lastmod' => null],
        ]);

        $groups = Chapter::query()
            ->where('status', ChapterStatus::Active)
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (Chapter $chapter): array => [
                'loc' => route('chapters.show', $chapter),
                'lastmod' => $chapter->updated_at,
            ]);

        $meetups = Event::query()
            ->whereHas('chapter', fn (Builder $query) => $query->where('status', ChapterStatus::Active))
            ->whereNull('canceled_at')
            ->where('ends_at', '>=', now())
            ->with('chapter:id,slug')
            ->orderBy('starts_at')
            ->get(['id', 'chapter_id', 'updated_at'])
            ->map(fn (Event $event): array => [
                'loc' => route('chapters.events.show', [$event->chapter, $event]),
                'lastmod' => $event->updated_at,
            ]);

        // The XML declaration is added here, not in the view: its closing question mark and angle bracket end PHP mode in Blade.
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .view('sitemap', ['urls' => $pages->concat($groups)->concat($meetups)])->render();

        return response($xml)->header('Content-Type', 'application/xml');
    }
}
