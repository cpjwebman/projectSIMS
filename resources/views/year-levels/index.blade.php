<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <p class="mb-1 small fw-semibold text-primary text-uppercase">Academic Management</p>
                <h1 class="h3 mb-0 fw-bold text-body">Year Levels</h1>
            </div>
            <a href="{{ route('year-levels.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add New Year Level</a>
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
                <form method="GET" action="{{ route('year-levels.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md">
                        <label for="search" class="form-label small fw-semibold">Search year levels</label>
                        <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Year level name or number" class="form-control">
                    </div>
                    <div class="col-12 col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>Search</button>
                        @if ($search !== '')
                            <a href="{{ route('year-levels.index') }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th scope="col" class="ps-3">#</th><th scope="col">Name</th><th scope="col">Level</th><th scope="col">Status</th><th scope="col" class="pe-3 text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($yearLevels as $yearLevel)
                            <tr>
                                <td class="ps-3 text-secondary">{{ $yearLevels->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $yearLevel->name ?: '—' }}</td>
                                <td><span class="badge text-bg-info">Year {{ $yearLevel->year_level }}</span></td>
                                <td><span class="badge rounded-pill text-bg-{{ $yearLevel->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($yearLevel->status) }}</span></td>
                                <td class="pe-3">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#year-level-details-{{ $yearLevel->id }}" aria-expanded="false" aria-controls="year-level-details-{{ $yearLevel->id }}"><i class="bi bi-eye me-1" aria-hidden="true"></i>View</button>
                                        <a href="{{ route('year-levels.edit', $yearLevel) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit</a>
                                        <form method="POST" action="{{ route('year-levels.destroy', $yearLevel) }}" onsubmit="return confirm('Delete this year level?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="p-0 border-0">
                                    <div class="collapse bg-body-tertiary px-4 py-3" id="year-level-details-{{ $yearLevel->id }}">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $yearLevel->name ?: '—' }}</dd>
                                        <dt class="col-sm-3">Year level</dt><dd class="col-sm-9 mb-0">{{ $yearLevel->year_level }}</dd>
                                    </dl>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-5 text-center text-secondary"><i class="bi bi-bar-chart-steps fs-3 d-block mb-2" aria-hidden="true"></i>No year levels found. Add a year level to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($yearLevels->hasPages())
                <div class="card-footer bg-white border-0">{{ $yearLevels->links('pagination::bootstrap-5') }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
