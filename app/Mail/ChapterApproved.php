<?php

namespace App\Mail;

use App\Models\Chapter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells a group's organizers it's approved and live, and points them at planning the first meetup.
 */
class ChapterApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Chapter $chapter)
    {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->chapter->name} is live on StreetWatchers",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.chapter-approved',
            text: 'mail.chapter-approved-text',
            with: [
                'groupUrl' => route('chapters.show', $this->chapter),
                'planMeetupUrl' => route('chapters.events.create', $this->chapter),
            ],
        );
    }
}
