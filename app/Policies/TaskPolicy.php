<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasAnyRole(['project-manager', 'finance'])) {
            return true;
        }

        return $task->assignee_id === $user->id
            || $task->project?->manager_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['project-manager', 'staff']);
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['project-manager', 'staff'])
            && ($task->assignee_id === $user->id || $user->hasAnyRole(['project-manager']));
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasRole('project-manager');
    }
}
