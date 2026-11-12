<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $now = now();
        $statuses = StudentEnrollment::query()
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->all();
        $statuses = array_values(array_unique([
            StudentEnrollment::STATUS_PENDING,
            StudentEnrollment::STATUS_APPROVED,
            ...$statuses,
        ]));
        $validated = $request->validate([
            'year' => ['sometimes', 'required', 'integer', 'between:1900,'.($now->year + 1)],
            'month' => ['sometimes', 'required', Rule::in(['all', ...range(1, 12)])],
            'status' => ['sometimes', 'required', Rule::in(['all', ...$statuses])],
        ]);

        $filters = [
            'year' => (int) ($validated['year'] ?? $now->year),
            'month' => (string) ($validated['month'] ?? 'all'),
            'status' => $validated['status'] ?? 'all',
        ];
        $applyFilters = static function (Builder $query, string $dateColumn = 'enrollment_date') use ($filters): void {
            $start = now()->setDate(
                $filters['year'],
                $filters['month'] === 'all' ? 1 : (int) $filters['month'],
                1
            )->startOfDay();
            $end = (clone $start)->endOfYear()->endOfDay();

            if ($filters['month'] !== 'all') {
                $end = (clone $start)->endOfMonth()->endOfDay();
            }

            $query->whereBetween($dateColumn, [$start, $end]);

            if ($filters['status'] !== 'all') {
                $query->where('status', $filters['status']);
            }
        };

        $enrollments = StudentEnrollment::query();
        $applyFilters($enrollments);
        $statusCounts = (clone $enrollments)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $statusCounts = collect($statuses)
            ->mapWithKeys(fn (string $status): array => [$status => (int) ($statusCounts[$status] ?? 0)]);

        $driver = DB::connection()->getDriverName();
        $monthExpression = match ($driver) {
            'sqlite' => "CAST(strftime('%m', enrollment_date) AS INTEGER)",
            'pgsql' => 'EXTRACT(MONTH FROM enrollment_date)',
            default => 'MONTH(enrollment_date)',
        };
        $createdMonthExpression = str_replace('enrollment_date', 'created_at', $monthExpression);

        $monthlyEnrollments = (clone $enrollments)
            ->selectRaw("$monthExpression as month, COUNT(*) as total")
            ->groupByRaw($monthExpression)
            ->pluck('total', 'month');
        $monthlyActivityQuery = StudentEnrollment::query();
        $applyFilters($monthlyActivityQuery, 'created_at');
        $monthlyActivity = $monthlyActivityQuery
            ->selectRaw("$createdMonthExpression as month, COUNT(*) as total")
            ->groupByRaw($createdMonthExpression)
            ->pluck('total', 'month');

        $courses = Course::query()
            ->withCount(['studentEnrollments as enrollments_count' => function (Builder $query) use ($applyFilters): void {
                $applyFilters($query);
            }])
            ->orderBy('name')
            ->get();

        $recentEnrollments = (clone $enrollments)
            ->with(['student', 'course', 'subjectEnrollments.subject'])
            ->orderByDesc('enrollment_date')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $yearExpression = match ($driver) {
            'sqlite' => "CAST(strftime('%Y', enrollment_date) AS INTEGER)",
            'pgsql' => 'EXTRACT(YEAR FROM enrollment_date)',
            default => 'YEAR(enrollment_date)',
        };
        $years = StudentEnrollment::query()
            ->whereNotNull('enrollment_date')
            ->selectRaw("$yearExpression as year")
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->push($now->year, $filters['year'])
            ->unique()
            ->sortDesc()
            ->values();

        $statistics = [
            'users' => User::count(),
            'students' => Student::count(),
            'enrolled_students' => Student::has('studentEnrollments')->count(),
            'unenrolled_students' => Student::doesntHave('studentEnrollments')->count(),
            'courses' => Course::count(),
            'subjects' => Subject::count(),
            'year_levels' => YearLevel::count(),
            'enrollments' => $enrollments->count(),
            'pending_enrollments' => $statusCounts[StudentEnrollment::STATUS_PENDING] ?? 0,
            'approved_enrollments' => $statusCounts[StudentEnrollment::STATUS_APPROVED] ?? 0,
        ];

        $chartData = [
            'months' => collect(range(1, 12))
                ->map(fn (int $month): string => $now->copy()->setDate($now->year, $month, 1)->format('M'))
                ->all(),
            'monthlyEnrollments' => collect(range(1, 12))
                ->map(fn (int $month): int => (int) ($monthlyEnrollments[$month] ?? 0))
                ->all(),
            'monthlyActivity' => collect(range(1, 12))
                ->map(fn (int $month): int => (int) ($monthlyActivity[$month] ?? 0))
                ->all(),
            'statusLabels' => $statusCounts->keys()->map(fn (string $status): string => ucfirst($status))->all(),
            'statusTotals' => $statusCounts->values()->all(),
            'courseLabels' => $courses->pluck('name')->all(),
            'courseEnrollments' => $courses->pluck('enrollments_count')->all(),
        ];

        return view('dashboard', compact('statistics', 'filters', 'statuses', 'years', 'courses', 'recentEnrollments', 'chartData'));
    }
}
