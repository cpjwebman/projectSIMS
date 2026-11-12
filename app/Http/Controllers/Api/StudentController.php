<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::query()
            ->with(['course', 'yearLevel'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);

        return StudentResource::collection($students);
    }

    public function show(Student $student): StudentResource
    {
        return new StudentResource($student->load(['course', 'yearLevel']));
    }

    public function store(Request $request): JsonResponse
    {
        $student = Student::create($this->validatedData($request));

        return (new StudentResource($student->load(['course', 'yearLevel'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Student $student): StudentResource
    {
        $student->update($this->validatedData($request, $student));

        return new StudentResource($student->refresh()->load(['course', 'yearLevel']));
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return response()->noContent();
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
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string'],
        ]);
    }
}
