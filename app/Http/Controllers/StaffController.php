<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('staff.view'), 403);

        $query = User::with('roles')
            ->withCount(['tasks as open_tasks_count' => fn ($q) => $q->whereIn('status', ['todo', 'in_progress', 'review'])])
            ->withCount(['projectsAsManager as managed_projects_count']);

        if ($role = $request->string('role')->value()) {
            $query->role($role);
        }

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        return view('staff.index', [
            'members' => $query->orderBy('name')->paginate(10)->withQueryString(),
            'roleCounts' => collect(User::ROLES)->mapWithKeys(
                fn ($label, $slug) => [$slug => User::role($slug)->count()]
            ),
            'taskOpenCount' => Task::visibleTo($request->user())->whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'projectCount' => Project::visibleTo($request->user())->whereIn('status', ['planning', 'in_progress'])->count(),
        ]);
    }

    public function store(StoreStaffRequest $request)
    {
        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);

        $user = User::create($data + ['is_active' => true, 'email_verified_at' => now()]);
        $user->syncRoles([$role]);

        Activity::log('created', "Invited {$user->name} as ".User::ROLES[$role], $user, 'user-plus');

        return redirect()->route('staff.index')->with('status', "{$user->name} was added to the team.");
    }

    public function toggle(User $user)
    {
        abort_unless(auth()->user()->can('staff.manage') && auth()->id() !== $user->id, 403);

        $user->update(['is_active' => ! $user->is_active]);

        Activity::log('updated', ($user->is_active ? 'Reactivated ' : 'Deactivated ').$user->name, $user, 'user-cog');

        return back()->with('status', $user->name.($user->is_active ? ' is active again.' : ' was deactivated.'));
    }

    /** Edit a team member - owner only. */
    public function update(Request $request, User $user)
    {
        abort_unless($request->user()->can('staff.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email,'.$user->id],
            'job_title' => ['nullable', 'string', 'max:120'],
            'role' => ['required', 'in:'.implode(',', array_keys(User::ROLES))],
        ]);

        $role = $data['role'];
        unset($data['role']);

        $user->update($data);
        $user->syncRoles([$role]);

        Activity::log('updated', "Updated team member {$user->name}", $user, 'user-cog');

        return back()->with('status', "{$user->name}'s details were updated.");
    }

    /** Remove a team member - owner only, never yourself. */
    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->can('staff.manage') && $request->user()->id !== $user->id, 403);

        $name = $user->name;
        $user->delete();

        Activity::log('deleted', "Removed team member {$name}", null, 'user-minus');

        return back()->with('status', "{$name} was removed from the team.");
    }
}