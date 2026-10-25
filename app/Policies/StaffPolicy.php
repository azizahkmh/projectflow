<?php

namespace App\Policies;

use App\Models\User;

/** Team / staff administration ability checks. */
class StaffPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo('staff.view');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('staff.manage');
    }
}
