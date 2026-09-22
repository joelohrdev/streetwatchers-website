<?php

use App\Models\Chapter;
use App\Models\Event;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * The SVG a QR code for this URL should be, so a test can tell which page a code links to.
 */
function qrCodeFor(string $url): string
{
    return (new Writer(new ImageRenderer(new RendererStyle(512), new SvgImageBackEnd)))->writeString($url);
}

test('anyone can get a QR code with the short link to a group page', function () {
    $group = Chapter::factory()->active()->create(['slug' => 'glasgow']);

    $response = $this->get(route('chapters.qr-code', $group))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml')
        ->assertHeader('Content-Disposition', 'inline; filename="glasgow-qr-code.svg"');

    expect($response->getContent())->toBe(qrCodeFor($group->shareUrl()));
});

test('anyone can get a QR code with the short link to a meetup page', function () {
    $group = Chapter::factory()->active()->create(['slug' => 'glasgow']);
    $meetup = Event::factory()->for($group)->upcoming()->create();

    $response = $this->get(route('chapters.events.qr-code', [$group, $meetup]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml');

    expect($response->getContent())->toBe(qrCodeFor($meetup->shareUrl()));
});

test('a QR code can be downloaded as a file', function () {
    $group = Chapter::factory()->active()->create(['slug' => 'glasgow']);
    $meetup = Event::factory()->for($group)->upcoming()->create();

    $this->get(route('chapters.events.qr-code', [$group, $meetup, 'download' => 1]))
        ->assertHeader('Content-Disposition', "attachment; filename=\"glasgow-meetup-{$meetup->id}-qr-code.svg\"");
});

test('there are no QR codes for groups that are not live, or meetups under the wrong group', function () {
    $pending = Chapter::factory()->pending()->create();
    $meetup = Event::factory()->for(Chapter::factory()->active())->upcoming()->create();
    $otherGroup = Chapter::factory()->active()->create();

    $this->get(route('chapters.qr-code', $pending))->assertNotFound();
    $this->get(route('chapters.events.qr-code', [$otherGroup, $meetup]))->assertNotFound();
});
