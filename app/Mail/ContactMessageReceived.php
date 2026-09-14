<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notifies the team about a contact form submission. Replies go straight to the sender.
 */
class ContactMessageReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public ContactMessage $contactMessage)
    {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->contactMessage->email, $this->contactMessage->name)],
            subject: "Contact form: {$this->contactMessage->topic->label()} from {$this->contactMessage->name}",
        );
    }

    /**
     * Get the message content definition.
     *
     * The message is rendered as escaped plain text rather than Markdown so senders cannot add links or formatting.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.contact-message-received',
            text: 'mail.contact-message-received-text',
            with: [
                'adminUrl' => route('admin.contact-messages.show', $this->contactMessage),
            ],
        );
    }
}
