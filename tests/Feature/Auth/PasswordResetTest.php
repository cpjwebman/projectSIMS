<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertOk()
        ->assertSee('login-card', false)
        ->assertSee('login-brand-panel', false)
        ->assertSee('Email Address')
        ->assertSee('Send Password Reset Link')
        ->assertSee('Back to Login');
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertOk()
            ->assertSee('login-card', false)
            ->assertSee('New password')
            ->assertSee('Confirm new password')
            ->assertSee('Reset Password')
            ->assertSee('Back to Login')
            ->assertSee('name="token"', false);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', __('passwords.reset'));

        expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        return true;
    });
});

test('reset token cannot be reused after a successful password reset', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $payload = [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ];

        $this->post('/reset-password', $payload)
            ->assertRedirect(route('login'));

        $this->from('/reset-password/'.$notification->token)
            ->post('/reset-password', $payload)
            ->assertSessionHasErrors('email');

        return true;
    });
});

test('password reset validates email token password rules and confirmation', function () {
    $this->from('/reset-password/invalid-token')
        ->post('/reset-password', [
            'token' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])
        ->assertRedirect('/reset-password/invalid-token')
        ->assertSessionHasErrors(['token', 'email', 'password']);
});

test('password reset link requests are rate limited', function () {
    Notification::fake();

    $user = User::factory()->create();

    foreach (range(1, 6) as $attempt) {
        $this->post('/forgot-password', ['email' => $user->email]);
    }

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertTooManyRequests();
});
