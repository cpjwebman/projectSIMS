<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use App\Models\YearLevel;

test('authenticated users can manage students', function () {
    $this->actingAs(User::factory()->create());
    $course = Course::create(['course_code' => 'STU-101', 'name' => 'Student Course']);
    $yearLevel = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);
    $studentUser = User::factory()->create();
    $studentUser->forceFill(['role' => User::ROLE_STUDENT, 'is_active' => false])->save();

    $studentData = [
        'student_number' => 'STU-1001',
        'first_name' => 'Avery',
        'last_name' => 'Morgan',
        'email' => 'avery@example.com',
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'user_id' => $studentUser->id,
        'phone' => '555-0101',
        'date_of_birth' => '2005-04-12',
        'address' => '12 College Road',
    ];

    $this->get(route('students.create'))->assertOk();
    $this->post(route('students.store'), $studentData)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.index'));

    $student = Student::where('student_number', 'STU-1001')->firstOrFail();

    $this->get(route('students.index'))
        ->assertOk()
        ->assertSee('Avery Morgan');
    $this->get(route('students.edit', $student))->assertOk();

    $this->from(route('students.create'))
        ->post(route('students.store'), $studentData)
        ->assertSessionHasErrors('student_number');

    $this->put(route('students.update', $student), [
        ...$studentData,
        'first_name' => 'Jordan',
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.index'));

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'first_name' => 'Jordan',
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'user_id' => $studentUser->id,
    ]);

    $this->delete(route('students.destroy', $student))
        ->assertRedirect(route('students.index'));

    $this->assertDatabaseMissing('students', ['id' => $student->id]);
});
