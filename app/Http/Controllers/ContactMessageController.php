<?php

namespace App\Http\Controllers;

use App\Enums\ContactTopic;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The public contact form.
 */
class ContactMessageController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('contact-messages/Create', [
            'topics' => ContactTopic::options(),
            'submitted' => $request->session()->get('status') === 'contact-message-sent',
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        // Spam bots fill the hidden field. Show them the usual confirmation without saving or emailing anything.
        if (! $request->isSpam()) {
            $contactMessage = ContactMessage::query()->create([
                ...$request->safe()->only(['name', 'email', 'topic', 'message']),
                'user_id' => $request->user()?->id,
            ]);

            $recipients = $this->recipients();

            // Without a contact address or an active super admin the message is still kept for the admin panel.
            if ($recipients !== []) {
                Mail::to($recipients)->queue(new ContactMessageReceived($contactMessage));
            }
        }

        return to_route('contact-messages.create')->with('status', 'contact-message-sent');
    }

    /**
     * The configured contact address, or every active super admin when none is set.
     *
     * @return array<int, string>
     */
    private function recipients(): array
    {
        $address = config('mail.contact_address');

        if (is_string($address) && $address !== '') {
            return [$address];
        }

        return User::query()
            ->where('role', UserRole::SuperAdmin)
            ->where('status', UserStatus::Active)
            ->get(['email'])
            ->map(fn (User $user): string => $user->email)
            ->all();
    }
}
