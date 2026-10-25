<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MetricsService
{
    /**
     * Tasks completed per month for the last $months months.
     *
     * @return array{labels: array<int,string>, completed: array<int,int>, created: array<int,int>}
     */
    public function tasksCompletedTimeline(int $months = 6): array
    {
        $labels = [];
        $completed = [];
        $created = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $labels[] = $month->format('M');

            $completed[] = Task::whereNotNull('completed_at')
                ->whereBetween('completed_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();

            $created[] = Task::whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();
        }

        return compact('labels', 'completed', 'created');
    }

    /**
     * Revenue vs. expenses per month, for the finance/cashflow chart.
     *
     * @return array{labels: array<int,string>, revenue: array<int,float>, expenses: array<int,float>, net: array<int,float>}
     */
    public function cashflowTimeline(int $months = 6): array
    {
        $labels = $revenue = $expenses = $net = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M');

            $in = (float) Order::where('status', 'paid')
                ->whereBetween('issued_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('total');

            $out = (float) Expense::where('status', 'approved')
                ->whereBetween('spent_on', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');

            $revenue[] = round($in, 2);
            $expenses[] = round($out, 2);
            $net[] = round($in - $out, 2);
        }

        return compact('labels', 'revenue', 'expenses', 'net');
    }

    public function financeSummary(): array
    {
        return [
            'revenue' => (float) Order::where('status', 'paid')->sum('total'),
            'outstanding' => (float) Order::whereIn('status', ['pending', 'overdue'])->sum('total'),
            'expenses' => (float) Expense::where('status', 'approved')->sum('amount'),
            'pending_expenses' => (float) Expense::where('status', 'pending')->sum('amount'),
            'budget' => (float) Project::sum('budget'),
        ];
    }

    public function expensesByCategory(): Collection
    {
        return Expense::where('status', 'approved')
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
    }

    public function projectHealth(): array
    {
        $total = Project::count();

        return [
            'total' => $total,
            'on_track' => Project::where('status', 'in_progress')->where('progress', '>=', 50)->count(),
            'at_risk' => Project::where('status', 'in_progress')
                ->where(fn ($q) => $q->where('progress', '<', 50)->orWhereDate('deadline', '<', now()))
                ->count(),
            'completed' => Project::where('status', 'completed')->count(),
        ];
    }

    /**
     * Project counts split by lifecycle status, for the dashboard donut.
     *
     * @return array{labels: array<int,string>, data: array<int,int>, colors: array<int,string>}
     */
    public function projectsByStatus(): array
    {
        $counts = Project::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $map = [
            'planning' => ['Planning', '#94A3B8'],
            'in_progress' => ['In Progress', '#A3E635'],
            'on_hold' => ['On Hold', '#FBBF24'],
            'completed' => ['Completed', '#10B981'],
            'cancelled' => ['Cancelled', '#F43F5E'],
        ];

        $labels = $data = $colors = [];

        foreach ($map as $status => [$label, $color]) {
            $count = (int) ($counts[$status] ?? 0);

            if ($count === 0) {
                continue;
            }

            $labels[] = $label;
            $data[] = $count;
            $colors[] = $color;
        }

        // Always show something rather than an empty canvas.
        if ($data === []) {
            $labels[] = 'No projects';
            $data[] = 1;
            $colors[] = '#E2E8F0';
        }

        return compact('labels', 'data', 'colors');
    }

    /**
     * Budget vs. spend for the top projects, for the horizontal bar diagram.
     *
     * @return array{labels: array<int,string>, budget: array<int,float>, spent: array<int,float>}
     */
    public function budgetVsSpend(int $limit = 6): array
    {
        $projects = Project::withSum('expenses as spent_total', 'amount')
            ->orderByDesc('budget')
            ->limit($limit)
            ->get();

        return [
            'labels' => $projects->pluck('name')->all(),
            'budget' => $projects->pluck('budget')->map(fn ($v) => (float) $v)->all(),
            'spent' => $projects->pluck('spent_total')->map(fn ($v) => (float) $v)->all(),
        ];
    }

    /**
     * Per-role delivery metrics normalised to 0-100 for the radar diagram.
     *
     * @return array{labels: array<int,string>, series: array<int,array{label:string,data:array<int,float>,color:string}>}
     */
    public function teamRadar(): array
    {
        $labels = ['Task volume', 'Completion', 'On-time', 'Projects led', 'Utilisation'];

        $maxTasks = max((int) Task::query()->count(), 1);

        $roleScores = [];

        $roles = [
            'staff' => ['Staff', '#84CC16'],
            'project-manager' => ['Managers', '#0EA5E9'],
        ];

        foreach ($roles as $slug => [$label, $color]) {
            $users = \App\Models\User::role($slug)->withCount([
                'tasks',
                'tasks as done_count' => fn ($q) => $q->where('status', 'done'),
            ])->get();

            $taskCount = (int) $users->sum('tasks_count');
            $doneCount = (int) $users->sum('done_count');

            $roleScores[] = [
                'label' => $label,
                'color' => $color,
                'data' => [
                    min(100, (int) round($taskCount / $maxTasks * 100)),
                    $taskCount ? (int) round($doneCount / $taskCount * 100) : 0,
                    $this->onTimeRate(),
                    min(100, (int) round($users->sum('managed_projects_count') / max(Project::count(), 1) * 100)),
                    min(100, (int) round($taskCount / max($users->count(), 1))),
                ],
            ];
        }

        return ['labels' => $labels, 'series' => $roleScores];
    }

    /** Percentage of completed tasks delivered on or before their due date. */
    public function onTimeRate(): int
    {
        $done = Task::where('status', 'done')->count();

        if ($done === 0) {
            return 0;
        }

        $onTime = Task::where('status', 'done')
            ->where(fn ($q) => $q->whereNull('due_date')->orWhereColumn('completed_at', '<=', 'due_date'))
            ->count();

        return (int) round($onTime / $done * 100);
    }
}
