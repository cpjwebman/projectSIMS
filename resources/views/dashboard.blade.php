@push('styles')
    <style>
        .dashboard-chart-container {
            position: relative;
            height: 18rem;
        }

        @media (max-width: 575.98px) {
            .dashboard-chart-container {
                height: 15rem;
            }
        }
    </style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <p class="mb-1 small fw-semibold text-primary text-uppercase">Student Management System</p>
                <h1 class="h3 mb-0 fw-bold text-body">Admin dashboard</h1>
            </div>
            <p class="mb-0 small text-secondary">{{ now()->format('l, F j, Y') }}</p>
        </div>
    </x-slot>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <section class="card border-0 shadow-sm mb-4" aria-labelledby="dashboard-filters-heading">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <h2 id="dashboard-filters-heading" class="h6 mb-1 fw-bold">Dashboard filters</h2>
                        <p class="small text-secondary mb-0">Filter enrollment metrics and activity by date and status.</p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset filters</a>
                </div>
                <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label for="filter-year" class="form-label small fw-semibold">Year</label>
                        <select id="filter-year" name="year" class="form-select">
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected($filters['year'] === (int) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label for="filter-month" class="form-label small fw-semibold">Month</label>
                        <select id="filter-month" name="month" class="form-select">
                            <option value="all" @selected($filters['month'] === 'all')>All months</option>
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}" @selected($filters['month'] === (string) $month)>{{ \Illuminate\Support\Carbon::createFromDate(2000, $month, 1)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label for="filter-status" class="form-label small fw-semibold">Request status</label>
                        <select id="filter-status" name="status" class="form-select">
                            <option value="all" @selected($filters['status'] === 'all')>All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-2" aria-hidden="true"></i>Apply filters</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="mb-4" aria-labelledby="dashboard-overview-heading">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 id="dashboard-overview-heading" class="h5 mb-0 fw-bold">Overview</h2>
                <span class="small text-secondary">{{ $filters['month'] === 'all' ? 'Full year' : \Illuminate\Support\Carbon::createFromDate(2000, (int) $filters['month'], 1)->format('F') }} {{ $filters['year'] }}</span>
            </div>
            <div class="row g-3">
                @php
                    $statCards = [
                        ['label' => 'Total users', 'value' => $statistics['users'], 'icon' => 'bi-people', 'color' => 'primary'],
                        ['label' => 'Total students', 'value' => $statistics['students'], 'icon' => 'bi-mortarboard', 'color' => 'success'],
                        ['label' => 'Total courses', 'value' => $statistics['courses'], 'icon' => 'bi-journal-bookmark', 'color' => 'info'],
                        ['label' => 'Enrollments in period', 'value' => $statistics['enrollments'], 'icon' => 'bi-clipboard-data', 'color' => 'dark'],
                        ['label' => 'Pending requests', 'value' => $statistics['pending_enrollments'], 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
                        ['label' => 'Approved requests', 'value' => $statistics['approved_enrollments'], 'icon' => 'bi-check2-circle', 'color' => 'success'],
                        ['label' => 'Students without enrollments', 'value' => $statistics['unenrolled_students'], 'icon' => 'bi-person-dash', 'color' => 'secondary'],
                        ['label' => 'Subjects', 'value' => $statistics['subjects'], 'icon' => 'bi-book', 'color' => 'danger'],
                    ];
                @endphp
                @foreach ($statCards as $card)
                    <div class="col-12 col-sm-6 col-xl-3">
                        <article class="card h-100 border-0 shadow-sm">
                            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <p class="small text-secondary mb-2">{{ $card['label'] }}</p>
                                    <p class="h3 fw-bold mb-0 text-body">{{ number_format($card['value']) }}</p>
                                </div>
                                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }} fs-4" style="width: 3rem; height: 3rem;">
                                    <i class="bi {{ $card['icon'] }}" aria-hidden="true"></i>
                                </span>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mb-4" aria-labelledby="dashboard-activity-heading">
            <h2 id="dashboard-activity-heading" class="h5 fw-bold mb-3">Enrollment activity</h2>
            <div class="row g-3">
                <div class="col-12 col-xl-8">
                    <article class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-3 p-lg-4">
                            <h3 class="h6 fw-bold mb-1">Monthly enrollments</h3>
                            <p class="small text-secondary mb-3">Enrollment dates by month for {{ $filters['year'] }}.</p>
                            <div class="dashboard-chart-container">
                                <canvas id="monthly-enrollments-chart" role="img" aria-label="Bar chart showing monthly enrollment totals"></canvas>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-xl-4">
                    <article class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-3 p-lg-4">
                            <h3 class="h6 fw-bold mb-1">Request status distribution</h3>
                            <p class="small text-secondary mb-3">Requests matching the selected filters.</p>
                            <div class="dashboard-chart-container">
                                <canvas id="status-distribution-chart" role="img" aria-label="Doughnut chart showing request counts by status"></canvas>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="mb-4" aria-labelledby="dashboard-breakdown-heading">
            <h2 id="dashboard-breakdown-heading" class="h5 fw-bold mb-3">Trends and breakdowns</h2>
            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <article class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-3 p-lg-4">
                            <h3 class="h6 fw-bold mb-1">Monthly request activity</h3>
                            <p class="small text-secondary mb-3">Requests created by month for {{ $filters['year'] }}.</p>
                            <div class="dashboard-chart-container">
                                <canvas id="monthly-activity-chart" role="img" aria-label="Line chart showing monthly request activity"></canvas>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-xl-6">
                    <article class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-3 p-lg-4">
                            <h3 class="h6 fw-bold mb-1">Enrollments by course</h3>
                            <p class="small text-secondary mb-3">Course distribution matching the selected filters.</p>
                            <div class="dashboard-chart-container">
                                <canvas id="course-distribution-chart" role="img" aria-label="Doughnut chart showing enrollments by course"></canvas>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="mb-4" aria-labelledby="course-totals-heading">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h2 id="course-totals-heading" class="h5 fw-bold mb-1">Enrollments per course</h2>
                    <p class="small text-secondary mb-0">Counts for the selected year, month, and status.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="btn btn-sm btn-outline-primary">Manage courses</a>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Course</th>
                                <th scope="col">Course code</th>
                                <th scope="col" class="pe-3 text-end">Enrollments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($courses as $course)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $course->name }}</td>
                                    <td>{{ $course->course_code }}</td>
                                    <td class="pe-3 text-end">{{ number_format($course->enrollments_count) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-5 text-center text-secondary">No courses have been created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section aria-labelledby="recent-activity-heading">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h2 id="recent-activity-heading" class="h5 fw-bold mb-1">Recent activity</h2>
                    <p class="small text-secondary mb-0">Latest enrollments within the selected filters.</p>
                </div>
                <a href="{{ route('enrollments.index') }}" class="btn btn-sm btn-outline-primary">View all requests</a>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Student</th>
                                <th scope="col">Course</th>
                                <th scope="col">Subject</th>
                                <th scope="col">Enrollment date</th>
                                <th scope="col" class="pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentEnrollments as $enrollment)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $enrollment->student?->first_name }} {{ $enrollment->student?->last_name }}</td>
                                    <td>{{ $enrollment->course?->name ?? 'Course unavailable' }}</td>
                                    <td>{{ $enrollment->subjectEnrollments->pluck('subject.name')->filter()->take(2)->join(', ') ?: '—' }}@if ($enrollment->subjectEnrollments->count() > 2)<span class="small text-secondary"> +{{ $enrollment->subjectEnrollments->count() - 2 }} more</span>@endif</td>
                                    <td>{{ $enrollment->enrollment_date?->format('M j, Y') }}</td>
                                    <td class="pe-3"><span class="badge rounded-pill text-bg-{{ $enrollment->status === \App\Models\StudentEnrollment::STATUS_APPROVED ? 'success' : ($enrollment->status === \App\Models\StudentEnrollment::STATUS_PENDING ? 'warning' : 'secondary') }}">{{ ucfirst($enrollment->status) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center text-secondary">No enrollment activity matches these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script>
        const dashboardCharts = {{ \Illuminate\Support\Js::from($chartData) }};
        const chartColors = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316'];
        const statusColors = dashboardCharts.statusLabels.map((label, index) => {
            const normalized = label.toLowerCase();
            if (normalized === 'pending') return '#f59e0b';
            if (normalized === 'approved' || normalized === 'completed') return '#10b981';
            if (normalized === 'rejected') return '#ef4444';
            return chartColors[index % chartColors.length];
        });
        const axisOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e5e7eb' } }
            }
        };

        new Chart(document.getElementById('monthly-enrollments-chart'), {
            type: 'bar',
            data: {
                labels: dashboardCharts.months,
                datasets: [{
                    label: 'Enrollments',
                    data: dashboardCharts.monthlyEnrollments,
                    backgroundColor: '#4f46e5',
                    borderRadius: 6,
                    maxBarThickness: 38
                }]
            },
            options: axisOptions
        });

        new Chart(document.getElementById('status-distribution-chart'), {
            type: 'doughnut',
            data: {
                labels: dashboardCharts.statusLabels,
                datasets: [{
                    data: dashboardCharts.statusTotals,
                    backgroundColor: statusColors,
                    borderColor: '#ffffff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 18 } } }
            }
        });

        new Chart(document.getElementById('monthly-activity-chart'), {
            type: 'line',
            data: {
                labels: dashboardCharts.months,
                datasets: [{
                    label: 'Requests created',
                    data: dashboardCharts.monthlyActivity,
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.12)',
                    pointBackgroundColor: '#0ea5e9',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: axisOptions
        });

        new Chart(document.getElementById('course-distribution-chart'), {
            type: 'doughnut',
            data: {
                labels: dashboardCharts.courseLabels,
                datasets: [{
                    data: dashboardCharts.courseEnrollments,
                    backgroundColor: dashboardCharts.courseLabels.map((_, index) => chartColors[index % chartColors.length]),
                    borderColor: '#ffffff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } }
            }
        });
    </script>
@endpush
</x-app-layout>
