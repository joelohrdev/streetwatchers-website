<?php

namespace App\Mail;

use App\Models\Chapter;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells site admins a new group is waiting for approval. Replies go to the person who proposed it.
 */
class ChapterProposed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Chapter $chapter, public User $proposer)
    {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->proposer->email, $this->proposer->name)],
            subject: "New group to review: {$this->chapter->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.chapter-proposed',
            text: 'mail.chapter-proposed-text',
            with: [
                'adminUrl' => route('admin.chapters.show', $this->chapter),
            ],
        );
    }
}
