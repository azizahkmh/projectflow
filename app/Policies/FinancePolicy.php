<?php

namespace App\Policies;

use App\Models\User;

/**
 * Finance-related ability checks: orders, expenses, reports.
 * Each ability defers to the permission matrix so roles stay the single
 * source of truth (use hasPermissionTo to avoid recursing through gates).
 */
class FinancePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewOrders(User $user): bool
    {
        return $user->hasPermissionTo('orders.view');
    }

    public function manageOrders(User $user): bool
    {
        return $user->hasPermissionTo('orders.manage');
    }

    public function viewFinance(User $user): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function manageFinance(User $user): bool
    {
        return $user->hasPermissionTo('finance.manage');
    }

    public function exportReports(User $user): bool
    {
        return $user->hasPermissionTo('reports.export');
    }
}
