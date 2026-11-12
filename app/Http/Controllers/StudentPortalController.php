<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Models\YearLevel;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentPortalController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        if ($student) {
            $student->loadMissing(['course', 'yearLevel']);
            $student->setRelation('studentEnrollments', $student->studentEnrollments()
                ->with(['course', 'yearLevel', 'subjectEnrollments.subject.course', 'subjectEnrollments.subject.yearLevel'])
                ->latest('enrollment_date')
                ->latest('id')
                ->get());
        }

        return view('student.dashboard', [
            'user' => $request->user(),
            'student' => $student,
            'courses' => Course::query()->where('status', 'active')->orderBy('name')->get(),
            'yearLevels' => YearLevel::query()->where('status', 'active')->orderBy('year_level')->get(),
        ]);
    }

    public function availableSubjects(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')->where('status', 'active')],
            'year_level_id' => ['required', 'integer', Rule::exists('year_levels', 'id')->where('status', 'active')],
        ]);

        $subjects = Subject::query()
            ->where('status', 'active')
            ->where('course_id', $validated['course_id'])
            ->where('year_level_id', $validated['year_level_id'])
            ->orderBy('subject_code')
            ->get(['id', 'subject_code', 'name', 'units', 'course_id', 'year_level_id']);

        return response()->json([
            'subjects' => $subjects->map(fn (Subject $subject) => [
                'id' => $subject->id,
                'code' => $subject->subject_code,
                'name' => $subject->name,
                'units' => (int) $subject->units,
            ])->values(),
        ]);
    }

    public function storeEnrollment(Request $request)
    {
        $student = $this->studentFor($request);
        $validated = $request->validate([
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')->where('status', 'active')],
            'year_level_id' => ['required', 'integer', Rule::exists('year_levels', 'id')->where('status', 'active')],
            'school_year' => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('student_enrollments', 'school_year')
                    ->where(fn ($query) => $query
                        ->where('student_id', $student->id)
                        ->where('course_id', $request->input('course_id'))
                        ->where('year_level_id', $request->input('year_level_id'))),
            ],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['required', 'integer', 'distinct', 'exists:subjects,id'],
        ]);

        $course = Course::query()->where('status', 'active')->findOrFail($validated['course_id']);
        $yearLevel = YearLevel::query()->where('status', 'active')->findOrFail($validated['year_level_id']);
        $subjects = Subject::query()
            ->whereIn('id', $validated['subject_ids'])
            ->where('status', 'active')
            ->where('course_id', $course->id)
            ->where('year_level_id', $yearLevel->id)
            ->get();

        if ($subjects->count() !== count($validated['subject_ids'])) {
            throw ValidationException::withMessages([
                'subject_ids' => 'Choose only active subjects assigned to the selected course and year level.',
            ]);
        }

        try {
            DB::transaction(function () use ($student, $course, $yearLevel, $validated, $subjects): void {
                $student->newQuery()->whereKey($student->id)->lockForUpdate()->firstOrFail();

                if (StudentEnrollment::query()
                    ->where('student_id', $student->id)
                    ->where('course_id', $course->id)
                    ->where('year_level_id', $yearLevel->id)
                    ->where('school_year', $validated['school_year'])
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'school_year' => 'You already have an enrollment for this course and year level in that school year.',
                    ]);
                }

                $enrollment = StudentEnrollment::query()->create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'year_level_id' => $yearLevel->id,
                    'school_year' => $validated['school_year'],
                    'enrollment_date' => now()->toDateString(),
                    'status' => StudentEnrollment::STATUS_PENDING,
                ]);

                foreach ($subjects as $subject) {
                    $enrollment->subjectEnrollments()->create([
                        'subject_id' => $subject->id,
                        'status' => StudentEnrollment::STATUS_PENDING,
                    ]);
                }
            });
        } catch (QueryException $exception) {
            if (! StudentEnrollment::query()
                ->where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->where('year_level_id', $yearLevel->id)
                ->where('school_year', $validated['school_year'])
                ->exists()) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'school_year' => 'You already have an enrollment for this course and year level in that school year.',
            ]);
        }

        return redirect()->route('student.dashboard')->with('status', 'Your enrollment request was submitted successfully.');
    }

    public function destroyEnrollment(Request $request, StudentEnrollment $enrollment)
    {
        $student = $this->studentFor($request);
        $ownedEnrollment = $student->studentEnrollments()
            ->whereKey($enrollment->id)
            ->firstOrFail();

        if ($ownedEnrollment->status !== StudentEnrollment::STATUS_PENDING) {
            return redirect()->route('student.dashboard')->withErrors([
                'enrollment' => 'Only pending enrollment requests can be cancelled.',
            ]);
        }

        DB::transaction(function () use ($ownedEnrollment): void {
            $ownedEnrollment->update(['status' => StudentEnrollment::STATUS_CANCELLED]);
            $ownedEnrollment->subjectEnrollments()->update(['status' => StudentEnrollment::STATUS_CANCELLED]);
        });

        return redirect()->route('student.dashboard')->with('status', 'Your enrollment request was cancelled; its history has been retained.');
    }

    private function studentFor(Request $request): Student
    {
        $student = $request->user()->student;

        abort_unless($student, 403, 'A student profile must be linked to your account.');

        return $student;
    }
}
