<?php

namespace App\Http\Controllers;

use App\Services\MetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(private MetricsService $metrics) {}

    public function __invoke(Request $request)
    {
        $user = $request->user();

        $projects = \App\Models\Project::visibleTo($user)
            ->with(['customer', 'manager'])
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done')]);

        $stats = [
            'active_projects' => (clone $projects)->whereIn('status', ['planning', 'in_progress'])->count(),
            'pending_tasks' => \App\Models\Task::visibleTo($user)->whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'revenue' => (float) \App\Models\Order::where('status', 'paid')->sum('total'),
            'expenses' => (float) \App\Models\Expense::where('status', 'approved')->sum('amount'),
        ];

        $recentProjects = (clone $projects)
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $timeline = $this->metrics->tasksCompletedTimeline(6);

        $taskMix = \App\Models\Task::visibleTo($user)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $upcoming = \App\Models\Task::visibleTo($user)
            ->with(['project', 'assignee'])
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'hideCharts' => $user->hasRole('staff'),
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'timeline' => $timeline,
            'taskMix' => $taskMix,
            'upcoming' => $upcoming,
            'statusDonut' => $this->metrics->projectsByStatus(),
            'budgetChart' => $this->metrics->budgetVsSpend(6),
            'taskOpenCount' => $stats['pending_tasks'],
            'projectCount' => $stats['active_projects'],
        ]);
    }
}
