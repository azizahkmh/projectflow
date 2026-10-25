<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('activity.view'), 403);

        $query = Activity::with('user')->latest();

        if ($action = $request->string('action')->value()) {
            $query->where('action', $action);
        }

        if ($userId = $request->integer('user')) {
            $query->where('user_id', $userId);
        }

        return view('activity.index', [
            'activities' => $query->paginate(15)->withQueryString(),
            'actions' => Activity::select('action')->distinct()->orderBy('action')->pluck('action'),
            'users' => \App\Models\User::orderBy('name')->get(['id', 'name', 'avatar_color']),
            'todayCount' => Activity::whereDate('created_at', today())->count(),
            'taskOpenCount' => Task::visibleTo($request->user())->whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'projectCount' => Project::visibleTo($request->user())->whereIn('status', ['planning', 'in_progress'])->count(),
        ]);
    }
}
