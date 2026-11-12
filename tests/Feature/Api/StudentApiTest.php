<?php

use App\Models\Course;
use App\Models\User;
use App\Models\YearLevel;

function studentApiToken(): string
{
    return User::factory()->create()->createToken('student-api-test')->plainTextToken;
}

test('token-authenticated users can use the student api crud endpoints', function () {
    $token = studentApiToken();
    $this->withToken($token);
    $course = Course::create(['course_code' => 'API-COURSE', 'name' => 'API Course']);
    $yearLevel = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);

    $data = [
        'student_number' => 'API-1001',
        'first_name' => 'Avery',
        'last_name' => 'Morgan',
        'email' => 'avery@example.com',
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'phone' => '555-0101',
        'date_of_birth' => '2005-04-12',
        'address' => '12 College Road',
    ];

    $this->getJson('/api/students')->assertOk();

    $createResponse = $this->postJson('/api/students', $data)
        ->assertCreated()
        ->assertJsonPath('data.student_number', 'API-1001')
        ->assertJsonPath('data.course.name', 'API Course')
        ->assertJsonPath('data.year_level.name', 'First Year');

    $studentId = $createResponse->json('data.id');

    $this->getJson("/api/students/{$studentId}")
        ->assertOk()
        ->assertJsonPath('data.first_name', 'Avery');

    $this->putJson("/api/students/{$studentId}", [
        ...$data,
        'first_name' => 'Jordan',
    ])
        ->assertOk()
        ->assertJsonPath('data.first_name', 'Jordan');

    $this->deleteJson("/api/students/{$studentId}")->assertNoContent();

    $this->assertDatabaseMissing('students', ['id' => $studentId]);
});

test('student api requires authentication', function () {
    $this->getJson('/api/students')->assertUnauthorized();
});

test('student api rejects invalid payloads without creating a record', function () {
    $this->withToken(studentApiToken())
        ->postJson('/api/students', [
            'student_number' => 'API-INVALID',
            'first_name' => '',
            'last_name' => 'Morgan',
            'email' => 'not-an-email',
            'course_id' => 999999,
            'year_level_id' => 999999,
            'phone' => str_repeat('1', 31),
            'date_of_birth' => '2999-01-01',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'first_name',
            'email',
            'course_id',
            'year_level_id',
            'phone',
            'date_of_birth',
        ]);

    $this->assertDatabaseMissing('students', ['student_number' => 'API-INVALID']);
});

test('student-role tokens cannot use the staff student api', function () {
    $user = User::factory()->create();
    $user->forceFill(['role' => User::ROLE_STUDENT])->save();
    $token = $user->createToken('student-role-test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/students')
        ->assertForbidden();
});

test('inactive users cannot access the student api', function () {
    $user = User::factory()->create();
    $user->forceFill(['is_active' => false])->save();
    $token = $user->createToken('inactive-test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/students')
        ->assertForbidden();
});
