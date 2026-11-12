<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <p class="mb-1 small fw-semibold text-primary text-uppercase">Academic Management</p>
                <h1 class="h3 mb-0 fw-bold text-body">Subject Details</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('subjects.edit', $subject) }}" class="btn btn-primary"><i class="bi bi-pencil me-2" aria-hidden="true"></i>Edit Subject</a>
                <a href="{{ route('subjects.index') }}" class="btn btn-outline-secondary">Back to subjects</a>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <section class="card border-0 shadow-sm mx-auto" style="max-width: 56rem;">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 border-bottom pb-4 mb-4">
                    <div>
                        <p class="small text-secondary mb-1">{{ $subject->subject_code }}</p>
                        <h2 class="h4 fw-bold mb-0">{{ $subject->name }}</h2>
                    </div>
                    <span class="badge rounded-pill text-bg-{{ $subject->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($subject->status) }}</span>
                </div>
                <dl class="row gy-3 mb-0">
                    <dt class="col-sm-4 text-secondary">Course</dt><dd class="col-sm-8">{{ $subject->course?->name ?: 'Unassigned' }}</dd>
                    <dt class="col-sm-4 text-secondary">Year level</dt><dd class="col-sm-8">{{ $subject->yearLevel ? 'Year '.$subject->yearLevel->year_level.' — '.$subject->yearLevel->name : 'Unassigned' }}</dd>
                    <dt class="col-sm-4 text-secondary">Units</dt><dd class="col-sm-8">{{ $subject->units }}</dd>
                    <dt class="col-sm-4 text-secondary">Description</dt><dd class="col-sm-8 mb-0">{{ $subject->description ?: '—' }}</dd>
                </dl>
            </div>
        </section>
    </div>
</x-app-layout>
