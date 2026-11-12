<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;

test('dashboard displays student enrollment and system statistics', function () {
    $user = User::factory()->create();
    $student = Student::create([
        'student_number' => 'STU-DASH-1',
        'first_name' => 'Dashboard',
        'last_name' => 'Student',
    ]);
    $course = Course::create([
        'course_code' => 'DASH-101',
        'name' => 'Dashboard Course',
    ]);
    $yearLevel = YearLevel::create([
        'year_level' => 1,
        'name' => 'First Year',
    ]);
    $subject = Subject::create([
        'subject_code' => 'DASH-SUB',
        'name' => 'Dashboard Subject',
        'units' => 3,
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

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Admin dashboard')
        ->assertSee('Dashboard filters')
        ->assertSee('Monthly enrollments')
        ->assertSee('Request status distribution')
        ->assertSee('Monthly request activity')
        ->assertSee('Enrollments by course')
        ->assertSee('Enrollments per course')
        ->assertSee('Recent activity')
        ->assertSee('Total users')
        ->assertSee('Total students')
        ->assertSee('Pending requests')
        ->assertSee('Approved requests')
        ->assertSee('1');
});

test('dashboard filters enrollment statistics and chart data using real records', function () {
    $user = User::factory()->create();
    $student = Student::create([
        'student_number' => 'STU-DASH-FILTER',
        'first_name' => 'Filtered',
        'last_name' => 'Student',
    ]);
    $currentCourse = Course::create([
        'course_code' => 'DASH-CURRENT',
        'name' => 'Current Course',
    ]);
    $previousCourse = Course::create([
        'course_code' => 'DASH-PREVIOUS',
        'name' => 'Previous Course',
    ]);
    $currentEnrollment = StudentEnrollment::create([
        'student_id' => $student->id,
        'course_id' => $currentCourse->id,
        'year_level_id' => YearLevel::create(['year_level' => 1, 'name' => 'First Year'])->id,
        'school_year' => now()->format('Y').'-'.now()->addYear()->format('Y'),
        'enrollment_date' => now()->toDateString(),
        'status' => StudentEnrollment::STATUS_PENDING,
    ]);
    $previousEnrollment = StudentEnrollment::create([
        'student_id' => $student->id,
        'course_id' => $previousCourse->id,
        'year_level_id' => YearLevel::create(['year_level' => 2, 'name' => 'Second Year'])->id,
        'school_year' => now()->subYear()->format('Y').'-'.now()->format('Y'),
        'enrollment_date' => now()->subYear()->toDateString(),
        'status' => StudentEnrollment::STATUS_APPROVED,
    ]);
    $previousEnrollment->forceFill(['created_at' => now()->subYear()])->save();

    $this->actingAs($user)
        ->get(route('dashboard', [
            'year' => now()->year,
            'month' => now()->month,
            'status' => StudentEnrollment::STATUS_PENDING,
        ]))
        ->assertOk()
        ->assertViewHas('statistics', fn (array $statistics): bool => $statistics['enrollments'] === 1
            && $statistics['pending_enrollments'] === 1
            && $statistics['approved_enrollments'] === 0)
        ->assertViewHas('chartData', function (array $chartData): bool {
            $monthIndex = (int) now()->format('n') - 1;

            return count($chartData['months']) === 12
                && $chartData['monthlyEnrollments'][$monthIndex] === 1
                && array_sum($chartData['monthlyEnrollments']) === 1
                && $chartData['statusLabels'] === ['Pending', 'Approved']
                && $chartData['statusTotals'] === [1, 0]
                && $chartData['courseLabels'] === ['Current Course', 'Previous Course']
                && $chartData['courseEnrollments'] === [1, 0];
        })
        ->assertViewHas('recentEnrollments', fn ($enrollments) => $enrollments->count() === 1)
        ->assertSee('Filtered Student')
        ->assertSee('Current Course')
        ->assertSee('Previous Course');
});
