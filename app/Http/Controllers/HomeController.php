<?php

namespace App\Http\Controllers;

use App\Enums\ChapterStatus;
use App\Models\Chapter;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * The marketing homepage.
     */
    public function __invoke(): Response
    {
        $activeChapters = Chapter::query()->where('status', ChapterStatus::Active);

        return Inertia::render('Home', [
            'chapterStats' => [
                'chapterCount' => (clone $activeChapters)->count(),
                'countryCount' => (clone $activeChapters)->distinct()->count('country'),
            ],
        ]);
    }
}
