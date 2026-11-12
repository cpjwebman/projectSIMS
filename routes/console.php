<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('users:make-admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error('No user found with that email address.');

        return 1;
    }

    $user->forceFill([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ])->save();

    $this->info("{$user->email} is now an active administrator.");

    return 0;
})->purpose('Promote an existing user to active administrator');
