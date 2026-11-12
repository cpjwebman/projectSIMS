@php($inputClass = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100')

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label for="student_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Student number</label>
        <input id="student_number" name="student_number" type="text" value="{{ old('student_number', $student?->student_number) }}" required maxlength="255" class="{{ $inputClass }}">
        @error('student_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $student?->email) }}" class="{{ $inputClass }}">
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="course_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course</label>
        <select id="course_id" name="course_id" required class="{{ $inputClass }}">
            <option value="">Select a course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected((string) old('course_id', $student?->course_id) === (string) $course->id)>{{ $course->course_code }} - {{ $course->name }}</option>
            @endforeach
        </select>
        @error('course_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="year_level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year level</label>
        <select id="year_level_id" name="year_level_id" required class="{{ $inputClass }}">
            <option value="">Select a year level</option>
            @foreach ($yearLevels as $yearLevel)
                <option value="{{ $yearLevel->id }}" @selected((string) old('year_level_id', $student?->year_level_id) === (string) $yearLevel->id)>Year {{ $yearLevel->year_level }}{{ $yearLevel->name ? ' - '.$yearLevel->name : '' }}</option>
            @endforeach
        </select>
        @error('year_level_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-2">
        <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Student login account</label>
        <select id="user_id" name="user_id" class="{{ $inputClass }}">
            <option value="">No linked account</option>
            @foreach ($studentUsers as $studentUser)
                <option value="{{ $studentUser->id }}" @selected((string) old('user_id', $student?->user_id) === (string) $studentUser->id)>{{ $studentUser->name }} ({{ $studentUser->email }}){{ $studentUser->is_active ? '' : ' - Inactive' }}</option>
            @endforeach
        </select>
        @error('user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First name</label>
        <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $student?->first_name) }}" required maxlength="255" class="{{ $inputClass }}">
        @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last name</label>
        <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $student?->last_name) }}" required maxlength="255" class="{{ $inputClass }}">
        @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
        <input id="phone" name="phone" type="tel" value="{{ old('phone', $student?->phone) }}" maxlength="30" class="{{ $inputClass }}">
        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of birth</label>
        <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $student?->date_of_birth?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}" class="{{ $inputClass }}">
        @error('date_of_birth') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-2">
        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
        <textarea id="address" name="address" rows="3" class="{{ $inputClass }}">{{ old('address', $student?->address) }}</textarea>
        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-5 dark:border-gray-700">
    <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-sm btn-primary">Save Student</button>
</div>