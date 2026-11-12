<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <p class="mb-1 small fw-semibold text-primary text-uppercase">Student Management</p>
                <h1 class="h3 mb-0 fw-bold text-body">Students</h1>
            </div>
            <a href="{{ route('students.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-2" aria-hidden="true"></i>Add New Student</a>
        </div>
    </x-slot>

    <div class="container-fluid px-3 px-lg-4 py-4">
        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center gap-2" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>{{ session('status') }}</span></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="card border-0 shadow-sm">
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('students.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md">
                        <label for="search" class="form-label small fw-semibold">Search students</label>
                        <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Student number, name, email, course, or year" class="form-control">
                    </div>
                    <div class="col-12 col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>Search</button>
                        @if ($search !== '')
                            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th scope="col" class="ps-3">#</th><th scope="col">Student number</th><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Course</th><th scope="col">Year level</th><th scope="col">Phone</th><th scope="col" class="pe-3 text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td class="ps-3 text-secondary">{{ $students->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold text-nowrap">{{ $student->student_number }}</td>
                                <td class="text-nowrap">{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td>{{ $student->email ?: '—' }}</td>
                                <td>{{ $student->course?->name ?: '—' }}</td>
                                <td class="text-nowrap">{{ $student->yearLevel ? 'Year '.$student->yearLevel->year_level : '—' }}</td>
                                <td class="text-nowrap">{{ $student->phone ?: '—' }}</td>
                                <td class="pe-3">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#student-details-{{ $student->id }}" aria-expanded="false" aria-controls="student-details-{{ $student->id }}"><i class="bi bi-eye me-1" aria-hidden="true"></i>View</button>
                                        <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit</a>
                                        <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('Delete this student?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" class="p-0 border-0">
                                    <div class="collapse bg-body-tertiary px-4 py-3" id="student-details-{{ $student->id }}">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">Student number</dt><dd class="col-sm-9">{{ $student->student_number }}</dd>
                                        <dt class="col-sm-3">Full name</dt><dd class="col-sm-9">{{ $student->first_name }} {{ $student->last_name }}</dd>
                                        <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $student->email ?: '—' }}</dd>
                                        <dt class="col-sm-3">Course / year level</dt><dd class="col-sm-9">{{ $student->course?->name ?: '—' }} / {{ $student->yearLevel ? 'Year '.$student->yearLevel->year_level : '—' }}</dd>
                                        <dt class="col-sm-3">Phone</dt><dd class="col-sm-9 mb-0">{{ $student->phone ?: '—' }}</dd>
                                    </dl>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-5 text-center text-secondary"><i class="bi bi-mortarboard fs-3 d-block mb-2" aria-hidden="true"></i>No students found. Add a student to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($students->hasPages())
                <div class="card-footer bg-white border-0">{{ $students->links('pagination::bootstrap-5') }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
