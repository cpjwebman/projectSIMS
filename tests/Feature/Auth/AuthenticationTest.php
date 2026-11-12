<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('Student Management System')
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('Remember me')
        ->assertSee('Forgot password?')
        ->assertSee('Create an account')
        ->assertSee('Show password')
        ->assertSee('Signing in...')
        ->assertSee('bootstrap-icons')
        ->assertSee('.login-brand-panel', false)
        ->assertSee('passwordToggle.setAttribute', false)
        ->assertSee('name="_token"', false);
});

test('authenticated users are redirected away from the login screen', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/login')
        ->assertRedirect(route('dashboard', absolute: false));
});

test('root redirects guests to login and authenticated users to their dashboard', function () {
    $this->get('/')
        ->assertRedirect(route('login', absolute: false));

    $staffUser = User::factory()->create();
    $this->actingAs($staffUser)
        ->get('/')
        ->assertRedirect(route('dashboard', absolute: false));

    $studentUser = User::factory()->create();
    $studentUser->forceFill(['role' => User::ROLE_STUDENT])->save();
    $this->actingAs($studentUser)
        ->get('/')
        ->assertRedirect(route('student.dashboard', absolute: false));
});

test('login validation errors are shown and the email is preserved', function () {
    $this->from('/login')
        ->post('/login', [
            'email' => 'not-an-email',
            'password' => '',
        ])
        ->assertRedirect('/login')
        ->assertSessionHasErrors(['email', 'password'])
        ->assertSessionHas('_old_input.email', 'not-an-email');

    $this->get('/login')
        ->assertOk()
        ->assertSee('value="not-an-email"', false)
        ->assertSee('The email field must be a valid email address.')
        ->assertSee('The password field is required.');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()->create();
    $user->forceFill(['is_active' => false])->save();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('student accounts are redirected to the student portal', function () {
    $user = User::factory()->create();
    $user->forceFill([
        'role' => User::ROLE_STUDENT,
        'is_active' => true,
    ])->save();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('student.dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
