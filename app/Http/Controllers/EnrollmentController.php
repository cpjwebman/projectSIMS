<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\YearLevel;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'year_level_id' => ['nullable', 'integer', 'exists:year_levels,id'],
            'school_year' => ['nullable', 'regex:/^\d{4}(?:-\d{4})?$/'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'cancelled'])],
        ]);
        $search = trim($validated['search'] ?? '');

        $query = StudentEnrollment::query()
            ->with(['student', 'course', 'yearLevel', 'subjectEnrollments.subject'])
            ->withCount('subjectEnrollments');

        foreach (['student_id', 'course_id', 'year_level_id', 'school_year', 'status'] as $filter) {
            if (($validated[$filter] ?? '') !== '') {
                $query->where($filter, $validated[$filter]);
            }
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('school_year', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($query) use ($search): void {
                        $query->where('student_number', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('course', function ($query) use ($search): void {
                        $query->where('course_code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('yearLevel', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%");
                        if (is_numeric($search)) {
                            $query->orWhere('year_level', $search);
                        }
                    })
                    ->orWhereHas('subjectEnrollments.subject', function ($query) use ($search): void {
                        $query->where('subject_code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        return view('enrollments.index', [
            'enrollments' => $query->latest('enrollment_date')->latest('id')->paginate(10)->withQueryString(),
            'search' => $search,
            'filters' => [
                'student_id' => (string) ($validated['student_id'] ?? ''),
                'course_id' => (string) ($validated['course_id'] ?? ''),
                'year_level_id' => (string) ($validated['year_level_id'] ?? ''),
                'school_year' => $validated['school_year'] ?? '',
                'status' => $validated['status'] ?? '',
            ],
            ...$this->filterOptions(),
        ]);
    }

    public function create()
    {
        return view('enrollments.create', [
            ...$this->formOptions(),
            'enrollment' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $this->saveEnrollment($validated);

        return redirect()->route('enrollments.index')->with('status', 'Student enrollment request created successfully.');
    }

    public function show(StudentEnrollment $enrollment)
    {
        $enrollment->load(['student', 'course', 'yearLevel', 'approver', 'subjectEnrollments.subject.course', 'subjectEnrollments.subject.yearLevel']);

        return view('enrollments.show', compact('enrollment'));
    }

    public function edit(StudentEnrollment $enrollment)
    {
        abort_unless($enrollment->status === StudentEnrollment::STATUS_PENDING, 403, 'Only pending enrollments can be edited.');

        $enrollment->load('subjectEnrollments');

        return view('enrollments.edit', [
            ...$this->formOptions(),
            'enrollment' => $enrollment,
        ]);
    }

    public function update(Request $request, StudentEnrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->status === StudentEnrollment::STATUS_PENDING, 403, 'Only pending enrollments can be edited.');

        $validated = $this->validatedData($request, $enrollment);
        $subjects = $this->eligibleSubjects($validated);

        DB::transaction(function () use ($enrollment, $validated, $subjects): void {
            $enrollment->update([
                'student_id' => $validated['student_id'],
                'course_id' => $validated['course_id'],
                'year_level_id' => $validated['year_level_id'],
                'school_year' => $validated['school_year'],
                'enrollment_date' => $validated['enrollment_date'],
            ]);
            $enrollment->subjectEnrollments()->delete();

            foreach ($subjects as $subject) {
                $enrollment->subjectEnrollments()->create([
                    'subject_id' => $subject->id,
                    'status' => StudentEnrollment::STATUS_PENDING,
                ]);
            }
        });

        return redirect()->route('enrollments.index')->with('status', 'Enrollment updated successfully.');
    }

    public function destroy(StudentEnrollment $enrollment): RedirectResponse
    {
        if ($enrollment->status !== StudentEnrollment::STATUS_PENDING) {
            return redirect()->route('enrollments.index')->withErrors([
                'enrollment' => 'Only pending enrollments can be cancelled.',
            ]);
        }

        DB::transaction(function () use ($enrollment): void {
            $enrollment->update(['status' => StudentEnrollment::STATUS_CANCELLED]);
            $enrollment->subjectEnrollments()->update(['status' => StudentEnrollment::STATUS_CANCELLED]);
        });

        return redirect()->route('enrollments.index')->with('status', 'Pending enrollment cancelled; its history has been retained.');
    }

    public function approve(StudentEnrollment $enrollment): RedirectResponse
    {
        if ($enrollment->status !== StudentEnrollment::STATUS_PENDING) {
            return redirect()->route('enrollments.index')->with('status', 'This enrollment has already been reviewed.');
        }

        DB::transaction(function () use ($enrollment): void {
            $enrollment->update([
                'status' => StudentEnrollment::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            $enrollment->subjectEnrollments()->update(['status' => StudentEnrollment::STATUS_APPROVED]);
        });

        return redirect()->route('enrollments.index')->with('status', 'Enrollment approved successfully.');
    }

    private function saveEnrollment(array $validated): StudentEnrollment
    {
        $subjects = $this->eligibleSubjects($validated);

        try {
            return DB::transaction(function () use ($validated, $subjects): StudentEnrollment {
                Student::query()->whereKey($validated['student_id'])->lockForUpdate()->firstOrFail();

                if (StudentEnrollment::query()
                    ->where('student_id', $validated['student_id'])
                    ->where('course_id', $validated['course_id'])
                    ->where('year_level_id', $validated['year_level_id'])
                    ->where('school_year', $validated['school_year'])
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'school_year' => 'This student already has an enrollment for the selected course and year level in that school year.',
                    ]);
                }

                $enrollment = StudentEnrollment::query()->create([
                    'student_id' => $validated['student_id'],
                    'course_id' => $validated['course_id'],
                    'year_level_id' => $validated['year_level_id'],
                    'school_year' => $validated['school_year'],
                    'enrollment_date' => $validated['enrollment_date'],
                    'status' => StudentEnrollment::STATUS_PENDING,
                ]);

                foreach ($subjects as $subject) {
                    $enrollment->subjectEnrollments()->create([
                        'subject_id' => $subject->id,
                        'status' => StudentEnrollment::STATUS_PENDING,
                    ]);
                }

                return $enrollment;
            });
        } catch (QueryException $exception) {
            if (! StudentEnrollment::query()
                ->where('student_id', $validated['student_id'])
                ->where('course_id', $validated['course_id'])
                ->where('year_level_id', $validated['year_level_id'])
                ->where('school_year', $validated['school_year'])
                ->exists()) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'school_year' => 'This student already has an enrollment for the selected course and year level in that school year.',
            ]);
        }
    }

    private function validatedData(Request $request, ?StudentEnrollment $enrollment = null): array
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')->where('status', 'active')],
            'year_level_id' => ['required', 'integer', Rule::exists('year_levels', 'id')->where('status', 'active')],
            'school_year' => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                Rule::unique('student_enrollments', 'school_year')
                    ->where(fn ($query) => $query
                        ->where('student_id', $request->input('student_id'))
                        ->where('course_id', $request->input('course_id'))
                        ->where('year_level_id', $request->input('year_level_id')))
                    ->ignore($enrollment?->id),
            ],
            'enrollment_date' => ['required', 'date'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['required', 'integer', 'distinct', 'exists:subjects,id'],
        ]);

        return $validated;
    }

    private function eligibleSubjects(array $validated)
    {
        $subjects = Subject::query()
            ->whereIn('id', $validated['subject_ids'])
            ->where('status', 'active')
            ->where('course_id', $validated['course_id'])
            ->where('year_level_id', $validated['year_level_id'])
            ->get();

        if ($subjects->count() !== count($validated['subject_ids'])) {
            throw ValidationException::withMessages([
                'subject_ids' => 'Choose only active subjects assigned to the selected course and year level.',
            ]);
        }

        return $subjects;
    }

    private function filterOptions(): array
    {
        return [
            'students' => Student::query()->orderBy('last_name')->orderBy('first_name')->get(['id', 'student_number', 'first_name', 'last_name']),
            'courses' => Course::query()->orderBy('name')->get(['id', 'course_code', 'name']),
            'yearLevels' => YearLevel::query()->orderBy('year_level')->get(['id', 'year_level', 'name']),
        ];
    }

    private function formOptions(): array
    {
        return [
            ...$this->filterOptions(),
            'subjects' => Subject::query()
                ->where('status', 'active')
                ->with(['course:id,name', 'yearLevel:id,name,year_level'])
                ->orderBy('subject_code')
                ->get(['id', 'subject_code', 'name', 'units', 'course_id', 'year_level_id']),
            'schoolYear' => now()->format('Y').'-'.now()->addYear()->format('Y'),
        ];
    }
}
