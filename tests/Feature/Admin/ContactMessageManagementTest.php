<?php

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('super admins can filter contact messages to unread ones', function () {
    $admin = User::factory()->superAdmin()->create();
    $unread = ContactMessage::factory()->create();
    ContactMessage::factory()->read()->create();

    $this->actingAs($admin)
        ->get(route('admin.contact-messages.index', ['unread' => 1]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/contact-messages/Index')
            ->has('messages.data', 1)
            ->where('messages.data.0.id', $unread->id)
            ->where('messages.data.0.is_read', false));
});

test('opening a contact message marks it as read', function () {
    $admin = User::factory()->superAdmin()->create();
    $contactMessage = ContactMessage::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.contact-messages.show', $contactMessage))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/contact-messages/Show')
            ->where('contactMessage.id', $contactMessage->id));

    expect($contactMessage->fresh()->read_at)->not->toBeNull();
});

test('the unread message count is shared with super admins only', function () {
    ContactMessage::factory(2)->create();
    ContactMessage::factory()->read()->create();

    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('unreadContactMessages', 2));

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('unreadContactMessages', null));
});

test('deleting a contact message removes it and logs the deletion without personal data', function () {
    $admin = User::factory()->superAdmin()->create();
    $contactMessage = ContactMessage::factory()->create(['email' => 'private@example.com']);

    $this->actingAs($admin)
        ->delete(route('admin.contact-messages.destroy', $contactMessage))
        ->assertRedirect(route('admin.contact-messages.index'));

    $this->assertModelMissing($contactMessage);
    expect(AuditLog::query()->sole())
        ->actor_id->toBe($admin->id)
        ->action->toBe(AuditAction::ContactMessageDeleted)
        ->subject_type->toBe('contact_message')
        ->subject_id->toBe($contactMessage->id)
        ->reason->toBeNull()
        ->metadata->toBe(['topic' => $contactMessage->topic->value]);
});
