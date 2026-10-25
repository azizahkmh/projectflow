<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private const COLUMNS = [
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'review' => 'In Review',
        'done' => 'Done',
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $this->authorize('viewAny', Task::class);

        $base = Task::visibleTo($user)->with(['project', 'assignee']);

        if ($search = $request->string('q')->trim()->value()) {
            $base->where('title', 'like', "%{$search}%");
        }

        if ($projectId = $request->integer('project')) {
            $base->where('project_id', $projectId);
        }

        if ($assignee = $request->integer('assignee')) {
            $base->where('assignee_id', $assignee);
        }

        $board = collect(self::COLUMNS)->map(
            fn ($label, $status) => (clone $base)->where('status', $status)->orderBy('due_date')->get()
        );

        return view('tasks.index', [
            'columns' => self::COLUMNS,
            'board' => $board,
            'projects' => Project::visibleTo($user)->orderBy('name')->get(['id', 'name']),
            'assignees' => User::role(['staff', 'project-manager'])->orderBy('name')->get(),
            'taskOpenCount' => Task::visibleTo($user)->whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'projectCount' => Project::visibleTo($user)->whereIn('status', ['planning', 'in_progress'])->count(),
        ]);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = Task::create($request->validated() + [
            'completed_at' => $request->input('status') === 'done' ? now() : null,
        ]);

        Activity::log('created', "Created task \"{$task->title}\"", $task, 'list-plus');

        return redirect()->route('tasks.index')->with('status', 'Task created successfully.');
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $data = $request->validated();

        if ($data['status'] === 'done' && ! $task->completed_at) {
            $data['completed_at'] = now();
        } elseif ($data['status'] !== 'done') {
            $data['completed_at'] = null;
        }

        $task->update($data);

        Activity::log('updated', "Updated task \"{$task->title}\" to {$task->status_label}", $task, 'check-circle');

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    /** Inline status change used by the kanban dropdown. */
    public function move(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'status' => ['required', 'in:todo,in_progress,review,done'],
        ]);

        $task->update([
            'status' => $data['status'],
            'completed_at' => $data['status'] === 'done' ? now() : null,
        ]);

        Activity::log('updated', "Moved \"{$task->title}\" to {$task->status_label}", $task, 'move-horizontal');

        return back()->with('status', 'Task moved.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $title = $task->title;
        $task->delete();

        Activity::log('deleted', "Deleted task \"{$title}\"", null, 'trash');

        return back()->with('status', 'Task deleted.');
    }
}
