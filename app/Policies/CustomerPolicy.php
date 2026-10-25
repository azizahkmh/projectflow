<?php

namespace App\Policies;

use App\Models\User;

/** Customers / CRM ability checks. */
class CustomerPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo('customers.view');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('customers.manage');
    }
}
