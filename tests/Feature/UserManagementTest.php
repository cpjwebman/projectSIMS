<?php

use App\Models\User;

function administrator(): User
{
    $user = User::factory()->create();
    $user->forceFill([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ])->save();

    return $user;
}

test('administrators can activate users and assign roles', function () {
    $admin = administrator();
    $pendingUser = User::factory()->create();
    $pendingUser->forceFill(['is_active' => false])->save();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($pendingUser->email)
        ->assertSee('id="user-edit-'.$pendingUser->id.'"', false)
        ->assertSee(route('users.update', $pendingUser), false);

    $this->patch(route('users.update', $pendingUser), [
        'role' => User::ROLE_STUDENT,
        'is_active' => '1',
    ])->assertRedirect();

    expect($pendingUser->fresh()->is_active)->toBeTrue();
    expect($pendingUser->fresh()->role)->toBe(User::ROLE_STUDENT);
});

test('staff cannot access user management', function () {
    $staff = User::factory()->create();

    $this->actingAs($staff)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('deactivated users cannot access protected routes', function () {
    $user = User::factory()->create();
    $user->forceFill(['is_active' => false])->save();

    $this->actingAs($user)
        ->get(route('students.index'))
        ->assertForbidden();
});

test('administrators cannot remove their own administrator access', function () {
    $admin = administrator();

    $this->actingAs($admin)
        ->patch(route('users.update', $admin), [
            'role' => User::ROLE_STAFF,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('user');

    expect($admin->fresh()->role)->toBe(User::ROLE_ADMIN);
});

test('console command can bootstrap an administrator', function () {
    $user = User::factory()->create();
    $user->forceFill(['is_active' => false])->save();

    $this->artisan('users:make-admin', ['email' => $user->email])
        ->assertSuccessful();

    expect($user->fresh()->role)->toBe(User::ROLE_ADMIN);
    expect($user->fresh()->is_active)->toBeTrue();
});
