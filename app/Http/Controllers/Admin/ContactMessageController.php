<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\ContactTopic;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function index(Request $request): Response
    {
        $topic = $request->enum('topic', ContactTopic::class);
        $unreadOnly = $request->boolean('unread');

        $messages = ContactMessage::query()
            ->when($topic, fn (Builder $query) => $query->where('topic', $topic))
            ->when($unreadOnly, fn (Builder $query) => $query->whereNull('read_at'))
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (ContactMessage $message): array => [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'topic' => $message->topic->label(),
                'excerpt' => Str::limit($message->message, 120),
                'is_read' => $message->read_at !== null,
                'created_at' => $message->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/contact-messages/Index', [
            'messages' => $messages,
            'filters' => ['topic' => $topic?->value, 'unread' => $unreadOnly],
            'topics' => ContactTopic::options(),
        ]);
    }

    /**
     * Show a message and mark it as read.
     */
    public function show(ContactMessage $contactMessage): Response
    {
        if ($contactMessage->read_at === null) {
            $contactMessage->forceFill(['read_at' => now()])->save();
        }

        return Inertia::render('admin/contact-messages/Show', [
            'contactMessage' => [
                'id' => $contactMessage->id,
                'name' => $contactMessage->name,
                'email' => $contactMessage->email,
                'topic' => $contactMessage->topic->label(),
                'message' => $contactMessage->message,
                'is_member' => $contactMessage->user_id !== null,
                'created_at' => $contactMessage->created_at?->toIso8601String(),
                'read_at' => $contactMessage->read_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Delete a message, for example to honour a request to erase personal data.
     */
    public function destroy(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        DB::transaction(function () use ($request, $contactMessage): void {
            $contactMessage->delete();

            // The audit entry deliberately leaves out the sender's name, email and message.
            AuditLog::record(
                actor: $request->user(),
                action: AuditAction::ContactMessageDeleted,
                subject: $contactMessage,
                metadata: ['topic' => $contactMessage->topic->value],
            );
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Message deleted.')]);

        return to_route('admin.contact-messages.index');
    }
}
