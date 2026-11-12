<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertOk()
        ->assertSee('Student Management System')
        ->assertSee('Full name')
        ->assertSee('Email address')
        ->assertSee('Confirm password')
        ->assertSee('Create Account')
        ->assertSee('Already have an account?')
        ->assertSee('href="'.route('login').'"', false)
        ->assertSee('name="_token"', false)
        ->assertSee('data-password-toggle="password"', false)
        ->assertSee('data-password-toggle="password_confirmation"', false)
        ->assertSee('.login-brand-panel', false)
        ->assertSee('.login-input-group', false);
});

test('authenticated users are redirected away from registration', function () {
    $this->actingAs(User::factory()->create())
        ->get('/register')
        ->assertRedirect(route('dashboard', absolute: false));
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'student',
        'is_active' => false,
    ]);
    expect(Hash::check('password', User::where('email', 'test@example.com')->firstOrFail()->password))
        ->toBeTrue();
});

test('registration validates unique email and confirmed password', function () {
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $this->from('/register')
        ->post('/register', [
            'name' => 'New Student',
            'email' => $existingUser->email,
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors(['email', 'password'])
        ->assertSessionHas('_old_input.name', 'New Student')
        ->assertSessionHas('_old_input.email', $existingUser->email)
        ->assertSessionMissing('_old_input.password')
        ->assertSessionMissing('_old_input.password_confirmation');

    $this->get('/register')
        ->assertOk()
        ->assertSee('value="New Student"', false)
        ->assertSee('value="existing@example.com"', false)
        ->assertSee('The email has already been taken.')
        ->assertSee('The password field confirmation does not match.');
});
