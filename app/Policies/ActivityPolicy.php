<?php

namespace App\Policies;

use App\Models\User;

/** Audit log access. */
class ActivityPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo('activity.view');
    }
}
