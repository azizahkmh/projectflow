<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Activity;
use App\Models\Expense;
use App\Models\Project;
use App\Services\MetricsService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(private MetricsService $metrics) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('finance.view'), 403);

        $query = Expense::with(['project', 'user'])->latest('spent_on');

        if ($category = $request->string('category')->value()) {
            $query->where('category', $category);
        }

        if ($projectId = $request->integer('project')) {
            $query->where('project_id', $projectId);
        }

        return view('finance.index', [
            'expenses' => $query->paginate(10)->withQueryString(),
            'summary' => $this->metrics->financeSummary(),
            'cashflow' => $this->metrics->cashflowTimeline(8),
            'byCategory' => $this->metrics->expensesByCategory(),
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'categories' => ['software', 'hardware', 'contractors', 'operations', 'marketing', 'travel', 'other'],
            'taskOpenCount' => \App\Models\Task::whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'projectCount' => Project::whereIn('status', ['planning', 'in_progress'])->count(),
        ]);
    }

    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create($request->validated() + ['user_id' => $request->user()->id]);

        Activity::log('created', "Logged expense \"{$expense->title}\" (".number_format((float) $expense->amount, 2).')', $expense, 'wallet');

        return redirect()->route('finance.index')->with('status', 'Expense recorded.');
    }

    public function approve(Request $request, Expense $expense)
    {
        abort_unless($request->user()->can('finance.manage'), 403);

        $expense->update(['status' => 'approved']);

        Activity::log('approved', "Approved expense \"{$expense->title}\"", $expense, 'check-circle-2');

        return back()->with('status', 'Expense approved.');
    }
    /** Edit an expense — finance / owner only. */
    public function update(StoreExpenseRequest $request, Expense $expense)
    {
        abort_unless($request->user()->can('finance.manage'), 403);

        $expense->update($request->validated());

        Activity::log('updated', "Edited expense \"{$expense->title}\"", $expense, 'wallet');

        return back()->with('status', 'Expense updated.');
    }

    /** Delete an expense — finance / owner only. */
    public function destroy(Request $request, Expense $expense)
    {
        abort_unless($request->user()->can('finance.manage'), 403);

        $title = $expense->title;
        $expense->delete();

        Activity::log('deleted', "Deleted expense \"{$title}\"", null, 'trash-2');

        return back()->with('status', "Expense \"{$title}\" deleted.");
    }
}
