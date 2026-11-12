<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('students can view only their linked profile and enrollments', function () {
    Storage::fake('public');

    $studentUser = User::factory()->create();
    $photoPath = UploadedFile::fake()->image('portal-profile.png')->store('profile-photos', 'public');
    $studentUser->forceFill([
        'role' => User::ROLE_STUDENT,
        'is_active' => true,
        'profile_photo_path' => $photoPath,
    ])->save();
    $otherStudentUser = User::factory()->create();
    $otherStudentUser->forceFill(['role' => User::ROLE_STUDENT])->save();

    $course = Course::create(['course_code' => 'PORTAL-101', 'name' => 'Portal Course']);
    $yearLevel = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);
    $subject = Subject::create([
        'subject_code' => 'PORTAL-SUB',
        'name' => 'Portal Subject',
        'units' => 3,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);
    $student = Student::create([
        'student_number' => 'PORTAL-1',
        'first_name' => 'Portal',
        'last_name' => 'Student',
        'user_id' => $studentUser->id,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);
    Student::create([
        'student_number' => 'PRIVATE-2',
        'first_name' => 'Other',
        'last_name' => 'Student',
        'user_id' => $otherStudentUser->id,
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

    $this->actingAs($studentUser)
        ->get('http://student-management.test'.route('student.dashboard', [], false))
        ->assertOk()
        ->assertSee('PORTAL-1')
        ->assertSee('Portal Subject')
        ->assertSee('http://student-management.test/storage/'.$photoPath, false)
        ->assertSee('alt="Profile photo for '.$studentUser->name.'"', false)
        ->assertSee('id="my-enrollments"', false)
        ->assertSee('My Enrollments')
        ->assertDontSee('PRIVATE-2')
        ->assertDontSee('System Summary');

    $this->get(route('students.index'))->assertForbidden();
    $this->get(route('dashboard'))->assertForbidden();
});

test('students can request multiple eligible subjects and cancel only pending enrollments', function () {
    $studentUser = User::factory()->create();
    $studentUser->forceFill([
        'role' => User::ROLE_STUDENT,
        'is_active' => true,
    ])->save();
    $course = Course::create(['course_code' => 'REQUEST-101', 'name' => 'Request Course']);
    $yearLevel = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);
    $subject = Subject::create([
        'subject_code' => 'REQUEST-SUB',
        'name' => 'Request Subject',
        'units' => 3,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);
    $secondSubject = Subject::create([
        'subject_code' => 'REQUEST-SUB-2',
        'name' => 'Request Subject Two',
        'units' => 2,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);
    $student = Student::create([
        'student_number' => 'REQUEST-1',
        'first_name' => 'Request',
        'last_name' => 'Student',
        'user_id' => $studentUser->id,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);

    $response = $this->actingAs($studentUser)
        ->post(route('student.enrollments.store'), [
            'course_id' => $course->id,
            'year_level_id' => $yearLevel->id,
            'school_year' => '2026-2027',
            'subject_ids' => [$subject->id, $secondSubject->id],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('student.dashboard'));

    $pendingEnrollment = StudentEnrollment::where('student_id', $student->id)->firstOrFail();
    expect($pendingEnrollment->status)->toBe(StudentEnrollment::STATUS_PENDING);
    expect($pendingEnrollment->subjectEnrollments)->toHaveCount(2);
    expect($pendingEnrollment->total_units)->toBe(5);

    $this->delete(route('student.enrollments.destroy', $pendingEnrollment))
        ->assertRedirect(route('student.dashboard'));
    $this->assertDatabaseHas('student_enrollments', [
        'id' => $pendingEnrollment->id,
        'status' => StudentEnrollment::STATUS_CANCELLED,
    ]);
    $this->assertDatabaseHas('subject_enrollments', [
        'student_enrollment_id' => $pendingEnrollment->id,
        'status' => StudentEnrollment::STATUS_CANCELLED,
    ]);

    $this->post(route('student.enrollments.store'), [
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'school_year' => '2026-2027',
        'subject_ids' => [$subject->id],
    ]);
    $approvedEnrollment = StudentEnrollment::where('student_id', $student->id)->firstOrFail();
    $approvedEnrollment->update(['status' => StudentEnrollment::STATUS_APPROVED]);

    $this->delete(route('student.enrollments.destroy', $approvedEnrollment))
        ->assertRedirect(route('student.dashboard'))
        ->assertSessionHasErrors('enrollment');
    $this->assertDatabaseHas('student_enrollments', ['id' => $approvedEnrollment->id]);

    $this->patch(route('enrollments.approve', $approvedEnrollment))
        ->assertForbidden();
});

test('students cannot enroll in unassigned subjects or duplicate the same course year and school year', function () {
    $user = User::factory()->create();
    $user->forceFill(['role' => User::ROLE_STUDENT, 'is_active' => true])->save();
    $course = Course::create(['course_code' => 'ELIGIBLE-101', 'name' => 'Eligible Course']);
    $otherCourse = Course::create(['course_code' => 'OTHER-101', 'name' => 'Other Course']);
    $level = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);
    $student = Student::create([
        'student_number' => 'ELIGIBLE-1',
        'first_name' => 'Eligible',
        'last_name' => 'Student',
        'user_id' => $user->id,
    ]);
    $eligible = Subject::create([
        'subject_code' => 'ELIGIBLE-SUB',
        'name' => 'Eligible Subject',
        'units' => 3,
        'course_id' => $course->id,
        'year_level_id' => $level->id,
    ]);
    $ineligible = Subject::create([
        'subject_code' => 'OTHER-SUB',
        'name' => 'Other Subject',
        'units' => 3,
        'course_id' => $otherCourse->id,
        'year_level_id' => $level->id,
    ]);

    $this->actingAs($user)
        ->getJson(route('student.enrollment-subjects', ['course_id' => $course->id, 'year_level_id' => $level->id]))
        ->assertOk()
        ->assertJsonCount(1, 'subjects')
        ->assertJsonPath('subjects.0.id', $eligible->id);

    $payload = [
        'course_id' => $course->id,
        'year_level_id' => $level->id,
        'school_year' => '2027-2028',
        'subject_ids' => [$eligible->id],
    ];
    $this->post(route('student.enrollments.store'), [...$payload, 'subject_ids' => [$ineligible->id]])
        ->assertSessionHasErrors('subject_ids');
    $this->post(route('student.enrollments.store'), $payload)->assertSessionHasNoErrors();
    $this->post(route('student.enrollments.store'), $payload)->assertSessionHasErrors('school_year');

    $otherLevel = YearLevel::create(['year_level' => 2, 'name' => 'Second Year']);
    $otherLevelSubject = Subject::create([
        'subject_code' => 'ELIGIBLE-2',
        'name' => 'Second Year Subject',
        'units' => 3,
        'course_id' => $course->id,
        'year_level_id' => $otherLevel->id,
    ]);
    $this->post(route('student.enrollments.store'), [
        ...$payload,
        'year_level_id' => $otherLevel->id,
        'subject_ids' => [$otherLevelSubject->id],
    ])->assertSessionHasNoErrors();

    expect(StudentEnrollment::where('student_id', $student->id)->count())->toBe(2);

    $otherStudent = Student::create([
        'student_number' => 'ELIGIBLE-2',
        'first_name' => 'Another',
        'last_name' => 'Student',
    ]);
    $otherEnrollment = StudentEnrollment::create([
        'student_id' => $otherStudent->id,
        'course_id' => $course->id,
        'year_level_id' => $level->id,
        'school_year' => '2028-2029',
        'enrollment_date' => now()->toDateString(),
    ]);
    $this->delete(route('student.enrollments.destroy', $otherEnrollment))->assertNotFound();
    $this->assertDatabaseHas('student_enrollments', ['id' => $otherEnrollment->id]);
});

test('students can update their profile and upload or remove a photo without changing role', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->forceFill([
        'role' => User::ROLE_STUDENT,
        'is_active' => true,
    ])->save();
    $course = Course::create(['course_code' => 'PROFILE-101', 'name' => 'Profile Course']);
    $yearLevel = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);
    $student = Student::create([
        'student_number' => 'PROFILE-1',
        'first_name' => 'Before',
        'last_name' => 'Update',
        'user_id' => $user->id,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ]);
    $profileData = [
        'name' => 'Updated Student',
        'email' => 'updated-student@example.com',
        'student_number' => 'PROFILE-1',
        'first_name' => 'Updated',
        'last_name' => 'Student',
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'phone' => '555-0199',
        'date_of_birth' => '2005-04-12',
        'address' => '12 College Road',
        'role' => User::ROLE_ADMIN,
    ];

    $this->actingAs($user)
        ->get(route('student.profile.edit'))
        ->assertOk()
        ->assertSee('Student');

    $this->patch(route('student.profile.update'), [
        ...$profileData,
        'profile_photo' => UploadedFile::fake()->image('profile.png'),
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('student.profile.edit'));

    $user->refresh();
    $student->refresh();
    expect($user->name)->toBe('Updated Student');
    expect($user->email)->toBe('updated-student@example.com');
    expect($user->role)->toBe(User::ROLE_STUDENT);
    expect($student->first_name)->toBe('Updated');
    expect($student->email)->toBe('updated-student@example.com');
    Storage::disk('public')->assertExists($user->profile_photo_path);

    $photoPath = $user->profile_photo_path;
    $this->patch(route('student.profile.update'), [
        ...$profileData,
        'remove_profile_photo' => '1',
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->profile_photo_path)->toBeNull();
    Storage::disk('public')->assertMissing($photoPath);
});

test('unlinked student account can create and then update one linked profile', function () {
    $user = User::factory()->create();
    $user->forceFill([
        'role' => User::ROLE_STUDENT,
        'is_active' => true,
    ])->save();
    $course = Course::create(['course_code' => 'SELF-101', 'name' => 'Self Service Course']);
    $yearLevel = YearLevel::create(['year_level' => 1, 'name' => 'First Year']);
    $data = [
        'name' => 'Self Service Student',
        'email' => $user->email,
        'student_number' => 'SELF-1',
        'first_name' => 'Self Service',
        'last_name' => 'Student',
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
    ];

    $this->actingAs($user)
        ->get(route('student.profile.edit'))
        ->assertOk()
        ->assertSee('Create Student Profile');

    $this->post(route('student.profile.store'), $data)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('student.dashboard'));

    $student = Student::where('user_id', $user->id)->firstOrFail();
    expect($student->student_number)->toBe('SELF-1');

    $this->patch(route('student.profile.update'), [
        ...$data,
        'student_number' => 'SELF-1-UPDATED',
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('student.profile.edit'));

    expect(Student::where('user_id', $user->id)->count())->toBe(1);
    expect($student->fresh()->student_number)->toBe('SELF-1-UPDATED');
});
