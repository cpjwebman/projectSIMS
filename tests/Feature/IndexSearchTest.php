<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;

test('resource index pages filter results using the search query', function () {
    $staff = User::factory()->create();
    $this->actingAs($staff);

    $course = Course::create([
        'course_code' => 'FIND-COURSE',
        'name' => 'Searchable Course',
        'description' => 'Course description',
    ]);
    $yearLevel = YearLevel::create([
        'year_level' => 3,
        'name' => 'Searchable Year',
    ]);
    $subject = Subject::create([
        'subject_code' => 'FIND-SUBJECT',
        'name' => 'Searchable Subject',
        'units' => 3,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);
    $student = Student::create([
        'student_number' => 'FIND-STUDENT',
        'first_name' => 'Searchable',
        'last_name' => 'Student',
        'email' => 'searchable@example.com',
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);
    $enrollment = StudentEnrollment::create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'school_year' => now()->format('Y').'-'.now()->addYear()->format('Y'),
        'enrollment_date' => now()->toDateString(),
    ]);
    $enrollment->subjectEnrollments()->create(['subject_id' => $subject->id]);

    $this->get(route('courses.index', ['search' => 'FIND-COURSE']))
        ->assertOk()
        ->assertSee('Searchable Course')
        ->assertSee(route('courses.edit', $course), false)
        ->assertSee(route('courses.destroy', $course), false)
        ->assertSee('course-details-'.$course->id, false);
    $this->get(route('students.index', ['search' => 'searchable@example.com']))
        ->assertOk()
        ->assertSee('Searchable Student')
        ->assertSee(route('students.edit', $student), false)
        ->assertSee(route('students.destroy', $student), false)
        ->assertSee('student-details-'.$student->id, false);
    $this->get(route('subjects.index', ['search' => 'FIND-SUBJECT']))
        ->assertOk()
        ->assertSee('Searchable Subject')
        ->assertSee(route('subjects.show', $subject), false)
        ->assertSee(route('subjects.edit', $subject), false)
        ->assertSee(route('subjects.destroy', $subject), false);
    $this->get(route('year-levels.index', ['search' => '3']))
        ->assertOk()
        ->assertSee('Searchable Year')
        ->assertSee(route('year-levels.edit', $yearLevel), false)
        ->assertSee(route('year-levels.destroy', $yearLevel), false)
        ->assertSee('year-level-details-'.$yearLevel->id, false);
    $this->get(route('enrollments.index', ['search' => 'FIND-STUDENT']))
        ->assertOk()
        ->assertSee('Searchable Student')
        ->assertSee(route('enrollments.show', $enrollment), false)
        ->assertSee(route('enrollments.edit', $enrollment), false)
        ->assertSee(route('enrollments.destroy', $enrollment), false);

    $admin = User::factory()->create();
    $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
    $inactiveUser = User::factory()->create();
    $inactiveUser->forceFill(['is_active' => false])->save();

    $this->actingAs($admin)
        ->get(route('users.index', ['search' => $admin->email]))
        ->assertOk()
        ->assertSee($admin->email)
        ->assertDontSee($staff->email)
        ->assertSee(route('users.update', $admin), false)
        ->assertSee('user-details-'.$admin->id, false)
        ->assertSee('user-edit-'.$admin->id, false);

    $inactiveUsersResponse = $this->get(route('users.index', ['search' => 'inactive']));

    $inactiveUsersResponse
        ->assertOk()
        ->assertSee($inactiveUser->email);
    expect($inactiveUsersResponse->viewData('users')->total())->toBe(1);
});

test('pagination retains the active search query', function () {
    $this->actingAs(User::factory()->create());

    foreach (range(1, 16) as $number) {
        Course::create([
            'course_code' => "PAGE-{$number}",
            'name' => sprintf('Pagination Course %02d', $number),
        ]);
    }

    $response = $this->get(route('courses.index', ['search' => 'Pagination']));

    $response->assertOk();
    expect($response->viewData('courses')->nextPageUrl())->toContain('search=Pagination');
    $response->assertSee('pagination', false);

    $this->get(route('courses.index', ['search' => 'Pagination', 'page' => 2]))
        ->assertOk()
        ->assertSee('Pagination Course 16')
        ->assertDontSee('Pagination Course 01');
});
