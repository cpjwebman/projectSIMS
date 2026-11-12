<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <p class="mb-1 small fw-semibold text-primary text-uppercase">Enrollment Management</p>
                <h1 class="h3 mb-0 fw-bold text-body">Student Enrollments</h1>
            </div>
            <a href="{{ route('enrollments.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-2" aria-hidden="true"></i>Create Enrollment</a>
        </div>
    </x-slot>

    <div class="container-fluid px-3 px-lg-4 py-4">
        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center gap-2" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>{{ session('status') }}</span></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="card border-0 shadow-sm">
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('enrollments.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-lg">
                        <label for="search" class="form-label small fw-semibold">Search enrollments</label>
                        <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Student, course, subject, school year, status" class="form-control">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="student_id" class="form-label small fw-semibold">Student</label>
                        <select id="student_id" name="student_id" class="form-select"><option value="">All students</option>
                            @foreach ($students as $student)<option value="{{ $student->id }}" @selected($filters['student_id'] === (string) $student->id)>{{ $student->student_number }} — {{ $student->first_name }} {{ $student->last_name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="course_id" class="form-label small fw-semibold">Course</label>
                        <select id="course_id" name="course_id" class="form-select"><option value="">All courses</option>
                            @foreach ($courses as $course)<option value="{{ $course->id }}" @selected($filters['course_id'] === (string) $course->id)>{{ $course->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="year_level_id" class="form-label small fw-semibold">Year level</label>
                        <select id="year_level_id" name="year_level_id" class="form-select"><option value="">All levels</option>
                            @foreach ($yearLevels as $yearLevel)<option value="{{ $yearLevel->id }}" @selected($filters['year_level_id'] === (string) $yearLevel->id)>Year {{ $yearLevel->year_level }} — {{ $yearLevel->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="school_year" class="form-label small fw-semibold">School year</label>
                        <input id="school_year" name="school_year" value="{{ $filters['school_year'] }}" pattern="[0-9]{4}(-[0-9]{4})?" class="form-control" placeholder="2026-2027 or 2026">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="status" class="form-label small fw-semibold">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach (['pending', 'approved', 'rejected', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>Filter</button>
                        @if ($search !== '' || collect($filters)->contains(fn ($value) => $value !== ''))
                            <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">#</th><th>Student</th><th>Course / year</th><th>School year</th><th>Subjects</th><th>Total units</th><th>Status</th><th class="pe-3 text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $enrollment)
                            @php($subjects = $enrollment->subjectEnrollments->filter(fn ($row) => $row->subject))
                            <tr>
                                <td class="ps-3 text-secondary">{{ $enrollments->firstItem() + $loop->index }}</td>
                                <td><div class="fw-semibold">{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}</div><div class="small text-secondary">{{ $enrollment->student->student_number }}</div></td>
                                <td><div class="fw-semibold">{{ $enrollment->course?->name ?: 'Course unavailable' }}</div><div class="small text-secondary">{{ $enrollment->yearLevel ? 'Year '.$enrollment->yearLevel->year_level.' — '.$enrollment->yearLevel->name : 'Year level not assigned' }}</div></td>
                                <td class="text-nowrap">{{ $enrollment->school_year }}</td>
                                <td>{{ $subjects->count() }}</td>
                                <td>{{ $subjects->sum(fn ($row) => (int) $row->subject->units) }}</td>
                                <td><span class="badge rounded-pill text-bg-{{ $enrollment->status === 'approved' ? 'success' : ($enrollment->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($enrollment->status) }}</span></td>
                                <td class="pe-3">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <a href="{{ route('enrollments.show', $enrollment) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye me-1" aria-hidden="true"></i>View</a>
                                        @if ($enrollment->status === \App\Models\StudentEnrollment::STATUS_PENDING)
                                            <form method="POST" action="{{ route('enrollments.approve', $enrollment) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Approve</button></form>
                                            <a href="{{ route('enrollments.edit', $enrollment) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit</a>
                                            <form method="POST" action="{{ route('enrollments.destroy', $enrollment) }}" onsubmit="return confirm('Cancel this pending enrollment?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1" aria-hidden="true"></i>Cancel</button></form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-5 text-center text-secondary"><i class="bi bi-clipboard2-check fs-3 d-block mb-2" aria-hidden="true"></i>No enrollments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($enrollments->hasPages())<div class="card-footer bg-white border-0">{{ $enrollments->links('pagination::bootstrap-5') }}</div>@endif
        </section>
    </div>
</x-app-layout>
