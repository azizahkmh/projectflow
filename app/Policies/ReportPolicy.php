<?php

namespace App\Policies;

use App\Models\User;

/** Saved reports. */
class ReportPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo('reports.view');
    }
}
