<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;

test('administrators can manage, filter, inspect, and approve normalized enrollments', function () {
    $this->actingAs(User::factory()->create());

    $student = Student::create([
        'student_number' => 'STU-2001',
        'first_name' => 'Sam',
        'last_name' => 'Taylor',
    ]);
    $course = Course::create(['course_code' => 'BIO-101', 'name' => 'Biology']);
    $yearLevel = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);
    $subject = Subject::create([
        'subject_code' => 'BIO-101-LAB',
        'name' => 'Biology Lab',
        'units' => 2,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);
    $secondSubject = Subject::create([
        'subject_code' => 'BIO-101-LECTURE',
        'name' => 'Biology Lecture',
        'units' => 3,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);
    $enrollmentData = [
        'student_id' => $student->id,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'school_year' => '2026-2027',
        'enrollment_date' => '2026-11-04',
        'subject_ids' => [$subject->id, $secondSubject->id],
    ];

    $this->get(route('enrollments.create'))->assertOk();
    $this->post(route('enrollments.store'), $enrollmentData)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('enrollments.index'));

    $enrollment = StudentEnrollment::where('student_id', $student->id)->firstOrFail();
    expect($enrollment->status)->toBe(StudentEnrollment::STATUS_PENDING);
    expect($enrollment->subjectEnrollments)->toHaveCount(2);

    $this->get(route('enrollments.index', ['course_id' => $course->id, 'year_level_id' => $yearLevel->id, 'school_year' => '2026-2027']))
        ->assertOk()
        ->assertSee('Biology')
        ->assertSee('Sam Taylor')
        ->assertSee('First Year');
    $this->get(route('enrollments.show', $enrollment))
        ->assertOk()
        ->assertSee('Biology Lecture')
        ->assertSee('Total units');
    $this->get(route('enrollments.edit', $enrollment))->assertOk();

    $this->from(route('enrollments.create'))
        ->post(route('enrollments.store'), $enrollmentData)
        ->assertSessionHasErrors('school_year');

    $this->put(route('enrollments.update', $enrollment), [
        ...$enrollmentData,
        'enrollment_date' => '2026-11-05',
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('enrollments.index'));

    expect($enrollment->fresh()->enrollment_date->toDateString())->toBe('2026-11-05');

    $this->patch(route('enrollments.approve', $enrollment))->assertRedirect(route('enrollments.index'));
    expect($enrollment->fresh()->status)->toBe(StudentEnrollment::STATUS_APPROVED);
    expect($enrollment->fresh()->approved_by)->toBe(auth()->id());
    $this->assertDatabaseHas('subject_enrollments', [
        'student_enrollment_id' => $enrollment->id,
        'status' => StudentEnrollment::STATUS_APPROVED,
    ]);

    $this->get(route('enrollments.edit', $enrollment))->assertForbidden();
    $this->delete(route('enrollments.destroy', $enrollment))
        ->assertRedirect(route('enrollments.index'))
        ->assertSessionHasErrors('enrollment');
    $this->assertDatabaseHas('student_enrollments', ['id' => $enrollment->id]);
});

test('a student can have separate course and year-level enrollments within the same school year', function () {
    $this->actingAs(User::factory()->create());
    $student = Student::create([
        'student_number' => 'STU-MULTI',
        'first_name' => 'Multi',
        'last_name' => 'Course',
    ]);
    $course = Course::create(['course_code' => 'MULTI-1', 'name' => 'Course One']);
    $otherCourse = Course::create(['course_code' => 'MULTI-2', 'name' => 'Course Two']);
    $firstLevel = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);
    $secondLevel = YearLevel::create(['year_level' => 2, 'name' => 'Second Year']);
    $firstSubject = Subject::create([
        'subject_code' => 'MULTI-S1',
        'name' => 'First Subject',
        'units' => 3,
        'course_id' => $course->id,
        'year_level_id' => $firstLevel->id,
    ]);
    $secondSubject = Subject::create([
        'subject_code' => 'MULTI-S2',
        'name' => 'Second Subject',
        'units' => 3,
        'course_id' => $otherCourse->id,
        'year_level_id' => $secondLevel->id,
    ]);

    foreach ([[$course, $firstLevel, $firstSubject], [$otherCourse, $secondLevel, $secondSubject]] as [$selectedCourse, $selectedLevel, $subject]) {
        $this->post(route('enrollments.store'), [
            'student_id' => $student->id,
            'course_id' => $selectedCourse->id,
            'year_level_id' => $selectedLevel->id,
            'school_year' => '2026-2027',
            'enrollment_date' => '2026-11-04',
            'subject_ids' => [$subject->id],
        ])->assertSessionHasNoErrors();
    }

    expect(StudentEnrollment::where('student_id', $student->id)->count())->toBe(2);
    $cancelled = StudentEnrollment::where('student_id', $student->id)->firstOrFail();
    $this->delete(route('enrollments.destroy', $cancelled))
        ->assertRedirect(route('enrollments.index'));
    expect($cancelled->fresh()->status)->toBe(StudentEnrollment::STATUS_CANCELLED);
    expect($cancelled->fresh()->subjectEnrollments()->firstOrFail()->status)->toBe(StudentEnrollment::STATUS_CANCELLED);
});
