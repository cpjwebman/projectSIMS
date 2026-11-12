<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($validated['search'] ?? '');

        $query = Course::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('course_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('courses.index', compact('courses', 'search'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        Course::create($this->validatedData($request));

        return redirect()->route('courses.index')->with('status', 'Course added successfully.');
    }

    public function edit(Course $course)
    {
        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $course->update($this->validatedData($request, $course));

        return redirect()->route('courses.index')->with('status', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        if ($course->enrollments()->exists() || $course->studentEnrollments()->exists() || $course->subjects()->exists() || $course->students()->exists()) {
            return redirect()->route('courses.index')
                ->withErrors(['course' => 'This course is referenced by subjects or enrollment history and cannot be deleted. Deactivate it instead.']);
        }

        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Course deleted successfully.');
    }

    private function validatedData(Request $request, ?Course $course = null): array
    {
        $validated = $request->validate([
            'course_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'course_code')->ignore($course?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        return [...$validated, 'status' => $validated['status'] ?? 'active'];
    }
}
