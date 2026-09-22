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
 * Tells a group's organizers it has been taken down. The admin's reason stays in the audit log, since it's
 * written for the team rather than for the organizers.
 */
class ChapterDeactivated extends Mailable implements ShouldQueue
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
            subject: "{$this->chapter->name} has been deactivated",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.chapter-deactivated',
            text: 'mail.chapter-deactivated-text',
            with: [
                'contactUrl' => route('contact-messages.create'),
            ],
        );
    }
}
