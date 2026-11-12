<?php

namespace App\Http\Controllers;

use App\Models\YearLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class YearLevelController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($validated['search'] ?? '');

        $query = YearLevel::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");

            if (is_numeric($search)) {
                $query->orWhere('year_level', $search);
            }
        }

        $yearLevels = $query
            ->orderBy('year_level')
            ->paginate(10)
            ->withQueryString();

        return view('year-levels.index', compact('yearLevels', 'search'));
    }

    public function create()
    {
        return view('year-levels.create');
    }

    public function store(Request $request)
    {
        YearLevel::create($this->validatedData($request));

        return redirect()->route('year-levels.index')->with('status', 'Year level added successfully.');
    }

    public function edit(YearLevel $yearLevel)
    {
        return view('year-levels.edit', compact('yearLevel'));
    }

    public function update(Request $request, YearLevel $yearLevel)
    {
        $yearLevel->update($this->validatedData($request, $yearLevel));

        return redirect()->route('year-levels.index')->with('status', 'Year level updated successfully.');
    }

    public function destroy(YearLevel $yearLevel)
    {
        if ($yearLevel->enrollments()->exists() || $yearLevel->studentEnrollments()->exists() || $yearLevel->subjects()->exists() || $yearLevel->students()->exists()) {
            return redirect()->route('year-levels.index')
                ->withErrors(['year_level' => 'This year level is referenced by subjects or enrollment history and cannot be deleted. Deactivate it instead.']);
        }

        $yearLevel->delete();

        return redirect()->route('year-levels.index')->with('status', 'Year level deleted successfully.');
    }

    private function validatedData(Request $request, ?YearLevel $yearLevel = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year_level' => [
                'required',
                'integer',
                'min:1',
                'max:255',
                Rule::unique('year_levels', 'year_level')->ignore($yearLevel?->id),
            ],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        return [...$validated, 'status' => $validated['status'] ?? 'active'];
    }
}
