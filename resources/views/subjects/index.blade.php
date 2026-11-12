<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <p class="mb-1 small fw-semibold text-primary text-uppercase">Academic Management</p>
                <h1 class="h3 mb-0 fw-bold text-body">Subjects</h1>
            </div>
            <a href="{{ route('subjects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add New Subject</a>
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
                <form method="GET" action="{{ route('subjects.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md">
                        <label for="search" class="form-label small fw-semibold">Search subjects</label>
                        <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Subject code, name, or description" class="form-control">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="course_id" class="form-label small fw-semibold">Course</label>
                        <select id="course_id" name="course_id" class="form-select">
                            <option value="">All courses</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected((string) $filters['course_id'] === (string) $course->id)>{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="year_level_id" class="form-label small fw-semibold">Year level</label>
                        <select id="year_level_id" name="year_level_id" class="form-select">
                            <option value="">All levels</option>
                            @foreach ($yearLevels as $yearLevel)
                                <option value="{{ $yearLevel->id }}" @selected((string) $filters['year_level_id'] === (string) $yearLevel->id)>Year {{ $yearLevel->year_level }} — {{ $yearLevel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="status" class="form-label small fw-semibold">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">All statuses</option>
                            <option value="active" @selected($filters['status'] === 'active')>Active</option>
                            <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>Search</button>
                        @if ($search !== '' || $filters['course_id'] !== '' || $filters['year_level_id'] !== '' || $filters['status'] !== '')
                            <a href="{{ route('subjects.index') }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th scope="col" class="ps-3">#</th><th scope="col">Subject code</th><th scope="col">Name</th><th scope="col">Course</th><th scope="col">Year level</th><th scope="col">Units</th><th scope="col">Status</th><th scope="col" class="pe-3 text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($subjects as $subject)
                            <tr>
                                <td class="ps-3 text-secondary">{{ $subjects->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold text-nowrap">{{ $subject->subject_code }}</td>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $subject->course?->name ?: 'Unassigned' }}</td>
                                <td>{{ $subject->yearLevel ? 'Year '.$subject->yearLevel->year_level.' — '.$subject->yearLevel->name : 'Unassigned' }}</td>
                                <td><span class="badge text-bg-primary">{{ $subject->units }} units</span></td>
                                <td><span class="badge rounded-pill text-bg-{{ $subject->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($subject->status) }}</span></td>
                                <td class="pe-3">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <a href="{{ route('subjects.show', $subject) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye me-1" aria-hidden="true"></i>View</a>
                                        <a href="{{ route('subjects.edit', $subject) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit</a>
                                        <form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('Delete this subject?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-5 text-center text-secondary"><i class="bi bi-book fs-3 d-block mb-2" aria-hidden="true"></i>No subjects found. Add a subject to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($subjects->hasPages())
                <div class="card-footer bg-white border-0">{{ $subjects->links('pagination::bootstrap-5') }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
