<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $this->authorize('viewAny', Project::class);

        $query = Project::visibleTo($user)
            ->with(['customer', 'manager'])
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done')]);

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$search}%")));
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        if ($priority = $request->string('priority')->value()) {
            $query->where('priority', $priority);
        }

        $projects = $query->latest('updated_at')->paginate(8)->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'customers' => Customer::orderBy('company_name')->get(),
            'managers' => User::role(['super-admin', 'project-manager'])->orderBy('name')->get(),
            'staff' => User::role(['staff', 'project-manager'])->orderBy('name')->get(),
            'counts' => [
                'all' => Project::visibleTo($user)->count(),
                'in_progress' => Project::visibleTo($user)->where('status', 'in_progress')->count(),
                'planning' => Project::visibleTo($user)->where('status', 'planning')->count(),
                'completed' => Project::visibleTo($user)->where('status', 'completed')->count(),
            ],
            'taskOpenCount' => \App\Models\Task::visibleTo($user)->whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'projectCount' => Project::visibleTo($user)->whereIn('status', ['planning', 'in_progress'])->count(),
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated() + [
            'slug' => null,
            'progress' => $request->integer('progress', 0),
        ]);

        if ($request->filled('staff')) {
            $project->staff()->sync($request->input('staff'));
        }

        Activity::log('created', "Created project \"{$project->name}\"", $project, 'folder-plus');

        return redirect()->route('projects.index')->with('status', "Project \"{$project->name}\" created.");
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['customer', 'manager', 'staff', 'tasks.assignee', 'expenses', 'orders']);

        return view('projects.show', [
            'project' => $project,
            'invoiced' => (float) $project->orders->sum('total'),
            'spent' => (float) $project->expenses->sum('amount'),
            'taskOpenCount' => Task::visibleTo(auth()->user())->whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'projectCount' => Project::visibleTo(auth()->user())->whereIn('status', ['planning', 'in_progress'])->count(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        if ($request->has('staff')) {
            $project->staff()->sync($request->input('staff', []));
        }

        Activity::log('updated', "Updated project \"{$project->name}\"", $project, 'folder-pen');

        return redirect()->route('projects.index')->with('status', "Project \"{$project->name}\" updated.");
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $name = $project->name;
        $project->delete();

        Activity::log('deleted', "Deleted project \"{$name}\"", null, 'trash');

        return redirect()->route('projects.index')->with('status', "Project \"{$name}\" deleted.");
    }
}
