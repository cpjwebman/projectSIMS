<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Student Portal</p>
            <h2 class="mt-1 text-xl font-semibold leading-tight text-gray-900 dark:text-gray-100">My Profile</h2>
        </div>
    </x-slot>

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            @if (session('status'))
                <div role="status" class="mb-5 border-l-4 border-emerald-600 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ $student ? route('student.profile.update') : route('student.profile.store') }}" enctype="multipart/form-data" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800 sm:p-8">
                @csrf
                @if ($student)
                    @method('PATCH')
                @endif

                @unless ($student)
                    <p class="border-l-4 border-amber-500 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:bg-amber-950 dark:text-amber-100">Create your student record to finish linking your account to the Student Portal.</p>
                @endunless

                <div class="flex flex-wrap items-center gap-5">
                    @if ($user->profile_photo_path)
                        <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="Profile photo for {{ $user->name }}" class="h-20 w-20 rounded-full object-cover">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-2xl font-semibold text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Student</p>
                    </div>
                    <div class="w-full sm:w-auto">
                        <label for="profile_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile photo</label>
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full max-w-sm text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300">
                        @error('profile_photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @if ($user->profile_photo_path)
                            <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input name="remove_profile_photo" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                Remove current photo
                            </label>
                        @endif
                    </div>
                </div>

                <div class="grid gap-6 border-t border-gray-200 pt-6 dark:border-gray-700 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-6 border-t border-gray-200 pt-6 dark:border-gray-700 sm:grid-cols-2">
                        <div>
                            <label for="student_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Student number</label>
                            <input id="student_number" name="student_number" type="text" value="{{ old('student_number', $student?->student_number) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            @error('student_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First name</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $student?->first_name) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last name</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $student?->last_name) }}" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $student?->phone) }}" maxlength="30" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of birth</label>
                            <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $student?->date_of_birth?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            @error('date_of_birth') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="course_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course</label>
                            <select id="course_id" name="course_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Select a course</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}" @selected((string) old('course_id', $student?->course_id) === (string) $course->id)>{{ $course->course_code }} - {{ $course->name }}</option>
                                @endforeach
                            </select>
                            @error('course_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="year_level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year level</label>
                            <select id="year_level_id" name="year_level_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Select a year level</option>
                                @foreach ($yearLevels as $yearLevel)
                                    <option value="{{ $yearLevel->id }}" @selected((string) old('year_level_id', $student?->year_level_id) === (string) $yearLevel->id)>Year {{ $yearLevel->year_level }}{{ $yearLevel->name ? ' - '.$yearLevel->name : '' }}</option>
                                @endforeach
                            </select>
                            @error('year_level_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                            <textarea id="address" name="address" rows="3" maxlength="2000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">{{ old('address', $student?->address) }}</textarea>
                            @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                </div>

                <div class="flex items-center justify-end border-t border-gray-200 pt-5 dark:border-gray-700">
                    <button type="submit" class="btn btn-sm btn-primary">{{ $student ? 'Save Profile' : 'Create Student Profile' }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>