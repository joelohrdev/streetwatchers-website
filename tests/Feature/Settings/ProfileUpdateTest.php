<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});

test('an Instagram handle is saved however it is pasted', function (string $entered) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => $user->name, 'email' => $user->email, 'instagram_handle' => $entered])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->instagram_handle)->toBe('street.walker_1');
})->with([
    'bare' => ['street.walker_1'],
    'with @' => ['@street.walker_1'],
    'mixed case' => ['@Street.Walker_1'],
    'profile link' => ['https://www.instagram.com/street.walker_1/'],
    'shared link' => ['instagram.com/street.walker_1?igsh=abc123'],
]);

test('an Instagram handle can be removed', function () {
    $user = User::factory()->create(['instagram_handle' => 'street.walker_1']);

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => $user->name, 'email' => $user->email, 'instagram_handle' => ''])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->instagram_handle)->toBeNull();
});

test('an Instagram handle must look like an Instagram username', function (string $entered, string $message) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => $user->name, 'email' => $user->email, 'instagram_handle' => $entered])
        ->assertSessionHasErrors(['instagram_handle' => $message]);

    expect($user->refresh()->instagram_handle)->toBeNull();
})->with([
    'spaces' => ['street walker', 'Instagram usernames only use letters, numbers, full stops and underscores.'],
    'too long' => [str_repeat('a', 31), 'Instagram usernames are 30 characters or fewer.'],
]);
