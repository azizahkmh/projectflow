<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Report;
use App\Models\Task;
use App\Services\MetricsService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private MetricsService $metrics) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $timeline = $this->metrics->tasksCompletedTimeline(9);

        $totalTasks = Task::count();
        $doneTasks = Task::where('status', 'done')->count();
        $onTime = Task::where('status', 'done')
            ->where(fn ($q) => $q->whereNull('due_date')->orWhereColumn('completed_at', '<=', 'due_date'))
            ->count();

        return view('reports.index', [
            'reports' => Report::with('generator')->latest('generated_at')->paginate(8),
            'timeline' => $timeline,
            'health' => $this->metrics->projectHealth(),
            'completionRate' => $totalTasks ? (int) round($doneTasks / $totalTasks * 100) : 0,
            'onTimeRate' => $doneTasks ? (int) round($onTime / $doneTasks * 100) : 0,
            'perProject' => Project::withCount([
                'tasks',
                'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
            ])->orderByDesc('budget')->limit(6)->get(),
            'statusDonut' => $this->metrics->projectsByStatus(),
            'budgetChart' => $this->metrics->budgetVsSpend(6),
            'teamRadar' => $this->metrics->teamRadar(),
            'cashflow' => $this->metrics->cashflowTimeline(9),
            'expenseMix' => $this->metrics->expensesByCategory(),
            'taskOpenCount' => Task::whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'projectCount' => Project::whereIn('status', ['planning', 'in_progress'])->count(),
        ]);
    }

    public function store(StoreReportRequest $request)
    {
        $report = Report::create($request->validated() + [
            'status' => 'ready',
            'generated_by' => $request->user()->id,
            'generated_at' => now(),
        ]);

        Activity::log('exported', "Generated report \"{$report->name}\"", $report, 'file-bar-chart');

        return redirect()->route('reports.index')->with('status', "Report \"{$report->name}\" generated.");
    }

    /**
     * "Export PDF" — streams a print-ready HTML document with the browser's
     * print-to-PDF stylesheet applied (no external binary required).
     */
    public function export(Request $request)
    {
        abort_unless($request->user()->can('reports.export'), 403);

        $timeline = $this->metrics->tasksCompletedTimeline(9);
        $health = $this->metrics->projectHealth();

        $projectRows = Project::with(['customer', 'manager'])
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done')])
            ->orderByDesc('budget')
            ->get();

        Activity::log('exported', 'Exported the project performance report as PDF', null, 'download');

        return response()
            ->view('reports.pdf', [
                'timeline' => $timeline,
                'health' => $health,
                'projects' => $projectRows,
                'generatedAt' => now(),
                'generatedBy' => $request->user(),
            ])
            ->header('Content-Disposition', 'inline; filename="projectflow-report-'.now()->format('Ymd-His').'.html"');
    }
}
