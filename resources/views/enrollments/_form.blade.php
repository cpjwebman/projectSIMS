<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="student_id" class="form-label fw-semibold">Student</label>
        <select id="student_id" name="student_id" required class="form-select">
            <option value="">Select a student</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected((string) old('student_id', $enrollment?->student_id) === (string) $student->id)>{{ $student->student_number }} — {{ $student->first_name }} {{ $student->last_name }}</option>
            @endforeach
        </select>
        @error('student_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="course_id" class="form-label fw-semibold">Course</label>
        <select id="course_id" name="course_id" required class="form-select">
            <option value="">Select a course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected((string) old('course_id', $enrollment?->course_id) === (string) $course->id)>{{ $course->course_code }} — {{ $course->name }}</option>
            @endforeach
        </select>
        @error('course_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="year_level_id" class="form-label fw-semibold">Year level</label>
        <select id="year_level_id" name="year_level_id" required class="form-select">
            <option value="">Select a year level</option>
            @foreach ($yearLevels as $yearLevel)
                <option value="{{ $yearLevel->id }}" @selected((string) old('year_level_id', $enrollment?->year_level_id) === (string) $yearLevel->id)>Year {{ $yearLevel->year_level }} — {{ $yearLevel->name }}</option>
            @endforeach
        </select>
        @error('year_level_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="school_year" class="form-label fw-semibold">School year</label>
        <input id="school_year" name="school_year" value="{{ old('school_year', $enrollment?->school_year ?? $schoolYear) }}" pattern="[0-9]{4}-[0-9]{4}" placeholder="2026-2027" required class="form-control">
        <div class="form-text">Use the format YYYY-YYYY.</div>
        @error('school_year')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="enrollment_date" class="form-label fw-semibold">Enrollment date</label>
        <input id="enrollment_date" name="enrollment_date" type="date" value="{{ old('enrollment_date', $enrollment?->enrollment_date?->format('Y-m-d') ?? now()->toDateString()) }}" required class="form-control">
        @error('enrollment_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <label class="form-label fw-semibold mb-0">Subjects</label>
            <span id="selected-units" class="badge text-bg-primary">0 selected units</span>
        </div>
        <div class="list-group" id="admin-subject-list">
            @php($oldSubjectIds = old('subject_ids'))
            @php($selectedSubjectIds = array_map('strval', is_array($oldSubjectIds) ? $oldSubjectIds : ($enrollment?->subjectEnrollments->pluck('subject_id')->all() ?? [])))
            @forelse ($subjects as $subject)
                <label class="list-group-item d-flex align-items-center gap-3" data-course="{{ $subject->course_id }}" data-year-level="{{ $subject->year_level_id }}">
                    <input class="form-check-input m-0 subject-option" type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" data-units="{{ $subject->units }}" @checked(in_array((string) $subject->id, $selectedSubjectIds, true))>
                    <span class="flex-grow-1"><span class="d-block fw-semibold">{{ $subject->subject_code }} — {{ $subject->name }}</span><span class="small text-secondary">{{ $subject->units }} units · {{ $subject->course?->name }} · {{ $subject->yearLevel ? 'Year '.$subject->yearLevel->year_level : 'Year level unavailable' }}</span></span>
                </label>
            @empty
                <div class="text-secondary p-3">No active subjects are configured yet.</div>
            @endforelse
        </div>
        @error('subject_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('subject_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <div class="form-text">Only subjects assigned to the selected course and year level can be selected.</div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 border-top pt-3">
    <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

@push('scripts')
    <script>
        (() => {
            const course = document.getElementById('course_id');
            const yearLevel = document.getElementById('year_level_id');
            const list = document.getElementById('admin-subject-list');
            const total = document.getElementById('selected-units');
            if (!course || !yearLevel || !list || !total) return;

            const update = () => {
                let units = 0;
                let selected = 0;
                for (const label of list.querySelectorAll('[data-course]')) {
                    const input = label.querySelector('input');
                    const available = label.dataset.course === course.value && label.dataset.yearLevel === yearLevel.value;
                    label.classList.toggle('d-none', !available);
                    input.disabled = !available;
                    if (!available) input.checked = false;
                    if (input.checked) {
                        units += Number(input.dataset.units);
                        selected++;
                    }
                }
                total.textContent = `${units} selected unit${units === 1 ? '' : 's'} (${selected} subjects)`;
            };

            course.addEventListener('change', update);
            yearLevel.addEventListener('change', update);
            list.addEventListener('change', update);
            update();
        })();
    </script>
@endpush
