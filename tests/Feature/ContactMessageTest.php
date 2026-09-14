<?php

use App\Enums\ContactTopic;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, string>
 */
function contactSubmission(array $overrides = []): array
{
    return [
        'name' => 'Alex Morgan',
        'email' => 'alex@example.com',
        'topic' => 'press',
        'message' => 'We would like to feature StreetWatchers in our magazine.',
        ...$overrides,
    ];
}

test('the contact page is available without logging in', function () {
    $this->get(route('contact-messages.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('contact-messages/Create')
            ->has('topics', count(ContactTopic::cases()))
            ->where('submitted', false));
});

test('a contact message is saved and emailed to the contact address', function () {
    Mail::fake();
    config(['mail.contact_address' => 'team@streetwatchers.test']);

    $this->post(route('contact-messages.store'), contactSubmission())
        ->assertRedirect(route('contact-messages.create'))
        ->assertSessionHas('status', 'contact-message-sent');

    $contactMessage = ContactMessage::query()->sole();

    expect($contactMessage)
        ->name->toBe('Alex Morgan')
        ->email->toBe('alex@example.com')
        ->topic->toBe(ContactTopic::Press)
        ->user_id->toBeNull()
        ->read_at->toBeNull();

    Mail::assertQueued(ContactMessageReceived::class, fn (ContactMessageReceived $mail) => $mail->contactMessage->is($contactMessage)
        && $mail->hasTo('team@streetwatchers.test')
        && $mail->hasReplyTo('alex@example.com', 'Alex Morgan'));
});

test('contact messages go to active super admins when no contact address is set', function () {
    Mail::fake();
    config(['mail.contact_address' => null]);
    $superAdmin = User::factory()->superAdmin()->create();
    $suspendedSuperAdmin = User::factory()->superAdmin()->suspended()->create();
    $member = User::factory()->create();

    $this->post(route('contact-messages.store'), contactSubmission());

    Mail::assertQueued(ContactMessageReceived::class, fn (ContactMessageReceived $mail) => $mail->hasTo($superAdmin->email)
        && ! $mail->hasTo($suspendedSuperAdmin->email)
        && ! $mail->hasTo($member->email));
});

test('a contact message is still saved when there is nobody to email', function () {
    Mail::fake();
    config(['mail.contact_address' => null]);

    $this->post(route('contact-messages.store'), contactSubmission())->assertSessionHasNoErrors();

    expect(ContactMessage::query()->count())->toBe(1);
    Mail::assertNothingQueued();
});

test('a signed-in member is linked to their contact message', function () {
    Mail::fake();
    $member = User::factory()->create();

    $this->actingAs($member)->post(route('contact-messages.store'), contactSubmission());

    expect(ContactMessage::query()->sole()->user_id)->toBe($member->id);
});

test('the contact form confirms the message was sent', function () {
    Mail::fake();

    $this->followingRedirects()
        ->post(route('contact-messages.store'), contactSubmission())
        ->assertInertia(fn (Assert $page) => $page->where('submitted', true));
});

test('a contact message requires a name, email, topic and message', function () {
    Mail::fake();

    $this->post(route('contact-messages.store'), [])
        ->assertSessionHasErrors([
            'name' => 'The name field is required.',
            'email' => 'The email field is required.',
            'topic' => 'Choose what your message is about.',
            'message' => 'The message field is required.',
        ]);

    expect(ContactMessage::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

test('a contact message rejects invalid details', function (array $overrides, string $field, string $message) {
    Mail::fake();

    $this->post(route('contact-messages.store'), contactSubmission($overrides))
        ->assertSessionHasErrors([$field => $message]);

    expect(ContactMessage::query()->count())->toBe(0);
})->with([
    'invalid email' => [['email' => 'not-an-email'], 'email', 'The email field must be a valid email address.'],
    'unknown topic' => [['topic' => 'lottery'], 'topic', 'The selected topic is invalid.'],
    'message too short' => [['message' => 'Hi'], 'message', 'Tell us a little more so we can help.'],
]);

test('submissions that fill the hidden spam field are not saved or emailed', function () {
    Mail::fake();

    $this->post(route('contact-messages.store'), contactSubmission(['website' => 'https://spam.example']))
        ->assertRedirect(route('contact-messages.create'))
        ->assertSessionHas('status', 'contact-message-sent');

    expect(ContactMessage::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

test('the contact email shows the message as plain text with a link to the admin panel', function () {
    $contactMessage = ContactMessage::factory()->create([
        'name' => 'Alex Morgan',
        'message' => '<script>alert(1)</script> [click me](https://phish.example)',
    ]);

    $mail = new ContactMessageReceived($contactMessage);

    $mail->assertSeeInHtml('&lt;script&gt;alert(1)&lt;/script&gt;', escape: false)
        ->assertDontSeeInHtml('<script>alert(1)</script>', escape: false)
        ->assertDontSeeInHtml('href="https://phish.example"', escape: false)
        ->assertSeeInHtml(route('admin.contact-messages.show', $contactMessage))
        ->assertSeeInText('Alex Morgan');
});
