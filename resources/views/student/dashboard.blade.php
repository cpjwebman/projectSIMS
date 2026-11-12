<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            @if ($user->profile_photo_path)
                <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="Profile photo for {{ $user->name }}" class="rounded-circle object-fit-cover" width="56" height="56">
            @else
                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            @endif
            <div>
                <p class="mb-1 small fw-semibold text-primary text-uppercase">Student Portal</p>
                <h1 class="h3 mb-0 fw-bold text-body">My Student Record</h1>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid px-3 px-lg-4 py-4">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="status">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <p class="fw-semibold mb-1">Please review the following:</p>
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if (! $student)
            <section class="alert alert-warning border-0 shadow-sm">
                <h2 class="h5 fw-semibold">Profile not linked yet</h2>
                <p class="mb-0">Your account is active, but an administrator has not linked a student record to it yet.</p>
            </section>
        @else
            <section aria-labelledby="student-profile-heading" class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 id="student-profile-heading" class="h5 fw-bold mb-3">Student Profile</h2>
                    <dl class="row gy-3 mb-0">
                        <div class="col-12 col-sm-6 col-lg-4"><dt class="small text-secondary">Student number</dt><dd class="fw-semibold mb-0">{{ $student->student_number }}</dd></div>
                        <div class="col-12 col-sm-6 col-lg-4"><dt class="small text-secondary">Name</dt><dd class="fw-semibold mb-0">{{ $student->first_name }} {{ $student->last_name }}</dd></div>
                        <div class="col-12 col-sm-6 col-lg-4"><dt class="small text-secondary">Email</dt><dd class="fw-semibold mb-0">{{ $student->email ?: $user->email }}</dd></div>
                        <div class="col-12 col-sm-6 col-lg-4"><dt class="small text-secondary">Profile course</dt><dd class="fw-semibold mb-0">{{ $student->course?->name ?: 'Not assigned' }}</dd></div>
                        <div class="col-12 col-sm-6 col-lg-4"><dt class="small text-secondary">Profile year level</dt><dd class="fw-semibold mb-0">{{ $student->yearLevel ? 'Year '.$student->yearLevel->year_level.' — '.$student->yearLevel->name : 'Not assigned' }}</dd></div>
                    </dl>
                </div>
            </section>

            <section id="my-enrollments" aria-labelledby="my-enrollments-heading" class="mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                    <div>
                        <p class="small text-primary text-uppercase fw-semibold mb-1">Academic history</p>
                        <h2 id="my-enrollments-heading" class="h4 fw-bold mb-0">My Enrollments</h2>
                    </div>
                </div>
                @forelse ($student->studentEnrollments->groupBy('school_year') as $schoolYear => $enrollments)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                            <h3 class="h6 fw-bold mb-0">School year {{ $schoolYear }}</h3>
                            <span class="badge text-bg-light border text-secondary">{{ $enrollments->count() }} course/year enrollment{{ $enrollments->count() === 1 ? '' : 's' }}</span>
                        </div>
                        <div class="card-body p-0">
                            @foreach ($enrollments as $enrollment)
                                @php($subjects = $enrollment->subjectEnrollments->filter(fn ($row) => $row->subject))
                                <article class="p-3 p-lg-4 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <h4 class="h6 fw-bold mb-1">{{ $enrollment->course?->name ?: 'Course unavailable' }}</h4>
                                            <p class="text-secondary small mb-0">{{ $enrollment->yearLevel ? 'Year '.$enrollment->yearLevel->year_level.' — '.$enrollment->yearLevel->name : 'Year level not assigned' }} <span class="mx-1">·</span> {{ $subjects->count() }} subjects <span class="mx-1">·</span> {{ $subjects->sum(fn ($row) => (int) $row->subject->units) }} units</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill text-bg-{{ $enrollment->status === 'approved' ? 'success' : ($enrollment->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($enrollment->status) }}</span>
                                            @if ($enrollment->status === \App\Models\StudentEnrollment::STATUS_PENDING)
                                                <form method="POST" action="{{ route('student.enrollments.destroy', $enrollment) }}" onsubmit="return confirm('Cancel this pending enrollment request?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="table-light"><tr><th>Code</th><th>Subject</th><th>Units</th><th>Status</th></tr></thead>
                                            <tbody>
                                                @forelse ($subjects as $subjectEnrollment)
                                                    <tr>
                                                        <td class="text-nowrap fw-semibold">{{ $subjectEnrollment->subject->subject_code }}</td>
                                                        <td>{{ $subjectEnrollment->subject->name }}</td>
                                                        <td>{{ $subjectEnrollment->subject->units }}</td>
                                                        <td><span class="badge rounded-pill text-bg-{{ $subjectEnrollment->status === 'approved' ? 'success' : ($subjectEnrollment->status === 'cancelled' ? 'secondary' : 'warning') }}">{{ ucfirst($subjectEnrollment->status) }}</span></td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center text-secondary py-3">No subjects are attached to this enrollment.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="card border-0 shadow-sm"><div class="card-body py-5 text-center text-secondary"><i class="bi bi-journal-check fs-2 d-block mb-2" aria-hidden="true"></i>No enrollment history yet.</div></div>
                @endforelse
            </section>

            <section aria-labelledby="enroll-heading" class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <p class="small text-primary text-uppercase fw-semibold mb-1">Registration</p>
                    <h2 id="enroll-heading" class="h4 fw-bold mb-2">Enroll in a Course</h2>
                    <p class="text-secondary mb-4">Choose an available course and year level, then select the active subjects you want to request.</p>

                    @if ($courses->isEmpty() || $yearLevels->isEmpty())
                        <div class="alert alert-info mb-0">There are no active courses or year levels available for enrollment right now.</div>
                    @else
                        <form method="POST" action="{{ route('student.enrollments.store') }}" id="student-enrollment-form" class="row g-3">
                            @csrf
                            <div class="col-12 col-md-6 col-lg-4">
                                <label for="course_id" class="form-label fw-semibold">Course</label>
                                <select id="course_id" name="course_id" class="form-select" required>
                                    <option value="">Select a course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>{{ $course->course_code }} — {{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label for="year_level_id" class="form-label fw-semibold">Year level</label>
                                <select id="year_level_id" name="year_level_id" class="form-select" required>
                                    <option value="">Select a year level</option>
                                    @foreach ($yearLevels as $yearLevel)
                                        <option value="{{ $yearLevel->id }}" @selected((string) old('year_level_id') === (string) $yearLevel->id)>Year {{ $yearLevel->year_level }} — {{ $yearLevel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label for="school_year" class="form-label fw-semibold">School year</label>
                                <input id="school_year" name="school_year" class="form-control" value="{{ old('school_year', now()->format('Y').'-'.now()->addYear()->format('Y')) }}" pattern="[0-9]{4}-[0-9]{4}" placeholder="2026-2027" required>
                                <div class="form-text">Use the format YYYY-YYYY.</div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <label class="form-label fw-semibold mb-0">Available subjects</label>
                                    <span id="selected-units" class="badge text-bg-primary">0 selected units</span>
                                </div>
                                <div id="subjects-message" class="rounded border bg-body-tertiary p-3 text-secondary" role="status">Select a course and year level to load eligible subjects.</div>
                                <div id="subjects-list" class="list-group d-none"></div>
                                @error('subject_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" id="submit-enrollment" class="btn btn-primary px-4" disabled><i class="bi bi-send me-2" aria-hidden="true"></i>Submit Enrollment</button>
                            </div>
                        </form>
                    @endif
                </div>
            </section>
        @endif
    </div>

    @if ($student)
        @push('scripts')
            <script>
                (() => {
                    const course = document.getElementById('course_id');
                    const yearLevel = document.getElementById('year_level_id');
                    const list = document.getElementById('subjects-list');
                    const message = document.getElementById('subjects-message');
                    const total = document.getElementById('selected-units');
                    const submit = document.getElementById('submit-enrollment');
                    const oldSubjectValues = @js(is_array(old('subject_ids')) ? array_map('strval', old('subject_ids')) : []);
                    const oldSelected = new Set(oldSubjectValues);

                    if (!course || !yearLevel || !list || !message || !total || !submit) return;

                    const updateTotal = () => {
                        const checked = [...list.querySelectorAll('input:checked')];
                        const units = checked.reduce((sum, input) => sum + Number(input.dataset.units), 0);
                        total.textContent = `${units} selected unit${units === 1 ? '' : 's'}`;
                        submit.disabled = checked.length === 0;
                    };

                    const loadSubjects = async () => {
                        list.replaceChildren();
                        list.classList.add('d-none');
                        submit.disabled = true;
                        total.textContent = '0 selected units';
                        if (!course.value || !yearLevel.value) {
                            message.textContent = 'Select a course and year level to load eligible subjects.';
                            message.classList.remove('d-none');
                            return;
                        }

                        message.textContent = 'Loading eligible subjects…';
                        message.classList.remove('d-none');
                        try {
                            const url = new URL(@js(route('student.enrollment-subjects')), window.location.origin);
                            url.searchParams.set('course_id', course.value);
                            url.searchParams.set('year_level_id', yearLevel.value);
                            const response = await fetch(url, { headers: { Accept: 'application/json' } });
                            if (!response.ok) throw new Error('Unable to load subjects. Please try again.');
                            const data = await response.json();

                            if (data.subjects.length === 0) {
                                message.textContent = 'No active subjects are available for this course and year level.';
                                return;
                            }

                            for (const subject of data.subjects) {
                                const label = document.createElement('label');
                                label.className = 'list-group-item d-flex align-items-center gap-3';
                                const checkbox = document.createElement('input');
                                checkbox.className = 'form-check-input m-0';
                                checkbox.type = 'checkbox';
                                checkbox.name = 'subject_ids[]';
                                checkbox.value = subject.id;
                                checkbox.dataset.units = subject.units;
                                checkbox.checked = oldSelected.has(String(subject.id));
                                checkbox.addEventListener('change', updateTotal);
                                const details = document.createElement('span');
                                details.className = 'flex-grow-1';
                                const title = document.createElement('span');
                                title.className = 'd-block fw-semibold';
                                title.textContent = `${subject.code} — ${subject.name}`;
                                const units = document.createElement('span');
                                units.className = 'small text-secondary';
                                units.textContent = `${subject.units} units`;
                                details.append(title, units);
                                label.append(checkbox, details);
                                list.append(label);
                            }
                            message.classList.add('d-none');
                            list.classList.remove('d-none');
                            updateTotal();
                            oldSelected.clear();
                        } catch (error) {
                            message.textContent = error.message;
                            message.classList.remove('d-none');
                        }
                    };

                    course.addEventListener('change', loadSubjects);
                    yearLevel.addEventListener('change', loadSubjects);
                    if (course.value && yearLevel.value) loadSubjects();
                })();
            </script>
        @endpush
    @endif
</x-app-layout>
