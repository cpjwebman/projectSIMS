<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <p class="mb-1 small fw-semibold text-primary text-uppercase">Course Management</p>
                <h1 class="h3 mb-0 fw-bold text-body">Courses</h1>
            </div>
            <a href="{{ route('courses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add New Course</a>
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
                <form method="GET" action="{{ route('courses.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md">
                        <label for="search" class="form-label small fw-semibold">Search courses</label>
                        <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Course code, name, or description" class="form-control">
                    </div>
                    <div class="col-12 col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>Search</button>
                        @if ($search !== '')
                            <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th scope="col" class="ps-3">#</th><th scope="col">Course code</th><th scope="col">Name</th><th scope="col">Description</th><th scope="col">Status</th><th scope="col" class="pe-3 text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($courses as $course)
                            <tr>
                                <td class="ps-3 text-secondary">{{ $courses->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold text-nowrap">{{ $course->course_code }}</td>
                                <td>{{ $course->name }}</td>
                                <td class="text-secondary">{{ $course->description ?: '—' }}</td>
                                <td><span class="badge rounded-pill text-bg-{{ $course->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($course->status) }}</span></td>
                                <td class="pe-3">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#course-details-{{ $course->id }}" aria-expanded="false" aria-controls="course-details-{{ $course->id }}"><i class="bi bi-eye me-1" aria-hidden="true"></i>View</button>
                                        <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit</a>
                                        <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Delete this course?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6" class="p-0 border-0">
                                    <div class="collapse bg-body-tertiary px-4 py-3" id="course-details-{{ $course->id }}">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">Course code</dt><dd class="col-sm-9">{{ $course->course_code }}</dd>
                                        <dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $course->name }}</dd>
                                        <dt class="col-sm-3">Description</dt><dd class="col-sm-9 mb-0">{{ $course->description ?: '—' }}</dd>
                                    </dl>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-5 text-center text-secondary"><i class="bi bi-journal-bookmark fs-3 d-block mb-2" aria-hidden="true"></i>No courses found. Add a course to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($courses->hasPages())
                <div class="card-footer bg-white border-0">{{ $courses->links('pagination::bootstrap-5') }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
