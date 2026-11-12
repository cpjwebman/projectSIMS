<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($validated['search'] ?? '');

        $query = Student::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('student_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('course_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('yearLevel', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");

                        if (is_numeric($search)) {
                            $query->orWhere('year_level', $search);
                        }
                    });
            });
        }

        $students = $query
            ->with(['course', 'yearLevel'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        return view('students.index', compact('students', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('students.create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Student::create($this->validatedData($request));

        return redirect()->route('students.index')->with('status', 'Student added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        return view('students.edit', [
            ...$this->formOptions($student),
            'student' => $student,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $student->update($this->validatedData($request, $student));

        return redirect()->route('students.index')->with('status', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        if ($student->enrollments()->exists() || $student->studentEnrollments()->exists()) {
            return redirect()->route('students.index')
                ->withErrors(['student' => 'This student has enrollment history and cannot be deleted.']);
        }

        $student->delete();

        return redirect()->route('students.index')->with('status', 'Student deleted successfully.');
    }

    private function validatedData(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'student_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('students', 'student_number')->ignore($student?->id),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'year_level_id' => ['required', 'integer', 'exists:year_levels,id'],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', User::ROLE_STUDENT),
                Rule::unique('students', 'user_id')->ignore($student?->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string'],
        ]);
    }

    private function formOptions(?Student $student = null): array
    {
        return [
            'courses' => Course::query()->orderBy('name')->get(),
            'yearLevels' => YearLevel::query()->orderBy('year_level')->get(),
            'studentUsers' => User::query()
                ->where('role', User::ROLE_STUDENT)
                ->where(fn ($query) => $query->whereDoesntHave('student')->orWhere('id', $student?->user_id ?? 0))
                ->orderBy('name')
                ->get(),
        ];
    }
}
