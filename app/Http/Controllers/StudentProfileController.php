<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\YearLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('student.profile', [
            'user' => $request->user(),
            'student' => $request->user()->student,
            'courses' => Course::query()->orderBy('name')->get(),
            'yearLevels' => YearLevel::query()->orderBy('year_level')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->student()->exists(), 409);

        $validated = $request->validate($this->rules($user));
        [$newPhotoPath, $removePhoto, $oldPhotoPath] = $this->updateAccount($request, $user, $validated);

        $student = DB::transaction(function () use ($user, $validated): Student {
            return $user->student()->create($this->studentData($validated));
        });
        $user->setRelation('student', $student);

        $this->deleteReplacedPhoto($oldPhotoPath, $newPhotoPath, $removePhoto);

        return Redirect::route('student.dashboard')->with('status', 'Student profile created and linked to your account.');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $student = $user->student;
        abort_unless($student, 404);

        $validated = $request->validate($this->rules($user, $student));
        [$newPhotoPath, $removePhoto, $oldPhotoPath] = $this->updateAccount($request, $user, $validated);

        DB::transaction(function () use ($user, $student, $validated, $newPhotoPath, $removePhoto): void {
            $this->saveAccount($user, $validated, $newPhotoPath, $removePhoto);
            foreach ($this->studentData($validated) as $attribute => $value) {
                $student->{$attribute} = $value;
            }
            $student->save();
        });

        $this->deleteReplacedPhoto($oldPhotoPath, $newPhotoPath, $removePhoto);

        return Redirect::route('student.profile.edit')->with('status', 'Student profile updated.');
    }

    private function rules($user, ?Student $student = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'student_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('students', 'student_number')->ignore($student?->id),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'year_level_id' => ['required', 'integer', 'exists:year_levels,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:2000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ];
    }

    private function updateAccount(Request $request, $user, array $validated): array
    {
        $oldPhotoPath = $user->profile_photo_path;
        $newPhotoPath = $request->file('profile_photo')?->store('profile-photos', 'public');
        $removePhoto = $request->boolean('remove_profile_photo') && ! $newPhotoPath;

        DB::transaction(function () use ($user, $validated, $newPhotoPath, $removePhoto): void {
            $this->saveAccount($user, $validated, $newPhotoPath, $removePhoto);
        });

        return [$newPhotoPath, $removePhoto, $oldPhotoPath];
    }

    private function saveAccount($user, array $validated, ?string $newPhotoPath, bool $removePhoto): void
    {
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($newPhotoPath) {
            $user->profile_photo_path = $newPhotoPath;
        } elseif ($removePhoto) {
            $user->profile_photo_path = null;
        }

        $user->save();
    }

    private function studentData(array $validated): array
    {
        return [
            'student_number' => $validated['student_number'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'course_id' => $validated['course_id'],
            'year_level_id' => $validated['year_level_id'],
            'phone' => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'address' => $validated['address'] ?? null,
        ];
    }

    private function deleteReplacedPhoto(?string $oldPhotoPath, ?string $newPhotoPath, bool $removePhoto): void
    {
        if ($oldPhotoPath && ($newPhotoPath || $removePhoto)) {
            Storage::disk('public')->delete($oldPhotoPath);
        }
    }
}
