<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Policies\ActivityPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\FinancePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ReportPolicy;
use App\Policies\StaffPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
    ];

    public function boot(): void
    {
        // Fine-grained finance/order/report abilities exposed as gates.
        Gate::define('orders.view', [FinancePolicy::class, 'viewOrders']);
        Gate::define('orders.manage', [FinancePolicy::class, 'manageOrders']);
        Gate::define('finance.view', [FinancePolicy::class, 'viewFinance']);
        Gate::define('finance.manage', [FinancePolicy::class, 'manageFinance']);
        Gate::define('reports.export', [FinancePolicy::class, 'exportReports']);

        // List / module access used by the sidebar and view-level @can checks.
        Gate::define('projects.view', [ProjectPolicy::class, 'viewAny']);
        Gate::define('projects.manage', fn ($user) => $user->hasAnyRole(['project-manager']));
        Gate::define('tasks.view', [TaskPolicy::class, 'viewAny']);
        Gate::define('tasks.manage', fn ($user) => $user->hasAnyRole(['project-manager', 'staff']));
        Gate::define('customers.view', [CustomerPolicy::class, 'view']);
        Gate::define('customers.manage', [CustomerPolicy::class, 'manage']);
        Gate::define('staff.view', [StaffPolicy::class, 'view']);
        Gate::define('staff.manage', [StaffPolicy::class, 'manage']);
        Gate::define('activity.view', [ActivityPolicy::class, 'view']);
        Gate::define('reports.view', [ReportPolicy::class, 'view']);

        // Super admins bypass every check.
        Gate::before(function ($user) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
