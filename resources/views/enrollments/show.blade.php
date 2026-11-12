<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div><p class="mb-1 small fw-semibold text-primary text-uppercase">Enrollment Management</p><h1 class="h3 mb-0 fw-bold text-body">Enrollment Details</h1></div>
            <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary">Back to enrollments</a>
        </div>
    </x-slot>
    @php($subjects = $enrollment->subjectEnrollments->filter(fn ($row) => $row->subject))
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="row g-4">
            <div class="col-12 col-xl-4">
                <section class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-4"><h2 class="h5 fw-bold mb-0">Enrollment Summary</h2><span class="badge rounded-pill text-bg-{{ $enrollment->status === 'approved' ? 'success' : ($enrollment->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($enrollment->status) }}</span></div>
                        
                        <dl class="row gy-3 mb-0">
                            <dt class="col-5 text-secondary"> Student</dt><dd class="col-7">
                               {{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}<div class="small text-secondary">{{ $enrollment->student->student_number }}</div></dd>
                            <dt class="col-5 text-secondary">Course</dt><dd class="col-7">{{ $enrollment->course?->name ?: 'Unavailable' }}</dd>
                            <dt class="col-5 text-secondary">Year level</dt><dd class="col-7">{{ $enrollment->yearLevel ? 'Year '.$enrollment->yearLevel->year_level.' — '.$enrollment->yearLevel->name : 'Unassigned' }}</dd>
                            <dt class="col-5 text-secondary">School year</dt><dd class="col-7">{{ $enrollment->school_year }}</dd>
                            <dt class="col-5 text-secondary">Enrollment date</dt><dd class="col-7">{{ $enrollment->enrollment_date?->format('M j, Y') }}</dd>
                            <dt class="col-5 text-secondary">Subjects</dt><dd class="col-7">{{ $subjects->count() }}</dd>
                            <dt class="col-5 text-secondary">Total units</dt><dd class="col-7">{{ $subjects->sum(fn ($row) => (int) $row->subject->units) }}</dd>
                            @if ($enrollment->approver)
                                <dt class="col-5 text-secondary">Reviewed by</dt><dd class="col-7 mb-0">{{ $enrollment->approver->name }}</dd>
                            @endif
                        </dl>
                        @if ($enrollment->status === \App\Models\StudentEnrollment::STATUS_PENDING)
                            <div class="d-flex gap-2 mt-4">
                                <form method="POST" action="{{ route('enrollments.approve', $enrollment) }}">@csrf @method('PATCH')<button class="btn btn-success" type="submit">Approve</button></form>
                                <a href="{{ route('enrollments.edit', $enrollment) }}" class="btn btn-outline-primary">Edit</a>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
            <div class="col-12 col-xl-8">
                <section class="card border-0 shadow-sm">
                    <div class="card-header bg-body py-3"><h2 class="h5 fw-bold mb-0">Enrolled Subjects</h2></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th class="ps-3">Code</th><th>Subject</th><th>Units</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse ($subjects as $subjectEnrollment)
                                    <tr><td class="ps-3 fw-semibold">{{ $subjectEnrollment->subject->subject_code }}</td><td>{{ $subjectEnrollment->subject->name }}</td><td>{{ $subjectEnrollment->subject->units }}</td><td><span class="badge rounded-pill text-bg-{{ $subjectEnrollment->status === 'approved' ? 'success' : ($subjectEnrollment->status === 'cancelled' ? 'secondary' : 'warning') }}">{{ ucfirst($subjectEnrollment->status) }}</span></td></tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-secondary py-5">No subjects are attached to this enrollment.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
