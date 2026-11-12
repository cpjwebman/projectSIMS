<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Course;
use App\Models\YearLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'year_level_id' => ['nullable', 'integer', 'exists:year_levels,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $search = trim($validated['search'] ?? '');
        $filters = [
            'course_id' => $validated['course_id'] ?? '',
            'year_level_id' => $validated['year_level_id'] ?? '',
            'status' => $validated['status'] ?? '',
        ];

        $query = Subject::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('subject_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($filters['course_id'] !== '') {
            $query->where('course_id', $filters['course_id']);
        }

        if ($filters['year_level_id'] !== '') {
            $query->where('year_level_id', $filters['year_level_id']);
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        $subjects = $query
            ->with(['course', 'yearLevel'])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('subjects.index', [
            'subjects' => $subjects,
            'search' => $search,
            'filters' => $filters,
            'courses' => Course::query()->orderBy('name')->get(),
            'yearLevels' => YearLevel::query()->orderBy('year_level')->get(),
        ]);
    }

    public function create(): View
    {
        return view('subjects.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        Subject::create($this->validatedData($request));

        return redirect()->route('subjects.index')->with('status', 'Subject added successfully.');
    }

    public function edit(Subject $subject): View
    {
        return view('subjects.edit', [
            ...$this->formOptions(),
            'subject' => $subject,
        ]);
    }

    public function show(Subject $subject): View
    {
        return view('subjects.show', [
            'subject' => $subject->load(['course', 'yearLevel']),
        ]);
    }

    public function update(Request $request, Subject $subject)
    {
        $subject->update($this->validatedData($request, $subject));

        return redirect()->route('subjects.index')->with('status', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->enrollments()->exists() || $subject->subjectEnrollments()->exists()) {
            return redirect()->route('subjects.index')
                ->withErrors(['subject' => 'This subject has enrollment history and cannot be deleted. Deactivate it instead.']);
        }

        $subject->delete();

        return redirect()->route('subjects.index')->with('status', 'Subject deleted successfully.');
    }

    private function validatedData(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'subject_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'subject_code')->ignore($subject?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'units' => ['required', 'integer', 'min:1', 'max:255'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'year_level_id' => ['required', 'integer', 'exists:year_levels,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function formOptions(): array
    {
        return [
            'courses' => Course::query()->orderBy('name')->get(),
            'yearLevels' => YearLevel::query()->orderBy('year_level')->get(),
        ];
    }
}
