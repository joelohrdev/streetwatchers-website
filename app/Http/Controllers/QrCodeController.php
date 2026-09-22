<?php

namespace App\Http\Controllers;

use App\Enums\ChapterStatus;
use App\Models\Chapter;
use App\Models\Event;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * QR codes linking to public group and meetup pages, for posters, flyers and phones at meetups. They encode
 * the short link, which makes a less dense code that's easier to scan, and they're drawn on our own server, so
 * the link never passes through a third party.
 */
class QrCodeController extends Controller
{
    /**
     * Width and height of the SVG. It's a vector, so it prints sharply at any size.
     */
    private const SIZE = 512;

    public function group(Request $request, Chapter $chapter): Response
    {
        abort_unless($chapter->status === ChapterStatus::Active, 404);

        return $this->qrCode($request, $chapter->shareUrl(), "{$chapter->slug}-qr-code.svg");
    }

    public function meetup(Request $request, Chapter $chapter, Event $event): Response
    {
        abort_unless($chapter->status === ChapterStatus::Active, 404);

        return $this->qrCode(
            $request,
            $event->shareUrl(),
            "{$chapter->slug}-meetup-{$event->id}-qr-code.svg",
        );
    }

    /**
     * An SVG QR code for the URL, shown in the page or, with ?download=1, saved as a file.
     */
    private function qrCode(Request $request, string $url, string $filename): Response
    {
        $svg = (new Writer(new ImageRenderer(new RendererStyle(self::SIZE), new SvgImageBackEnd)))->writeString($url);

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
