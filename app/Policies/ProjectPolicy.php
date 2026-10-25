<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /** Super admins can do anything. */
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    /** Every role with projects.view sees a (scoped) project list. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        // Managers and finance see every project.
        if ($user->hasAnyRole(['project-manager', 'finance'])) {
            return true;
        }

        // Staff see projects they manage or are assigned to.
        return $project->manager_id === $user->id
            || $project->staff()->whereKey($user->id)->exists();
    }

    /** Manager can create projects. Staff create tasks, not projects. */
    public function create(User $user): bool
    {
        return $user->hasRole('project-manager');
    }

    /**
     * Manager: any project. Staff: any project (edit only, never delete).
     * Finance: view-only, cannot edit.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['project-manager', 'staff']);
    }

    /** Only the manager (and the super-admin via before()) may delete. */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole('project-manager');
    }
}
