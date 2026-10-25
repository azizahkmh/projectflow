<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
            'orders.view', 'orders.manage',
            'finance.view', 'finance.manage',
            'reports.view', 'reports.export',
            'customers.view', 'customers.manage',
            'staff.view', 'staff.manage',
            'activity.view',
            'settings.simulate-role',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $matrix = [
            'super-admin' => $permissions,
            'project-manager' => [
                'projects.view', 'projects.create', 'projects.update', 'projects.delete',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
                'orders.view', 'orders.manage',
                'reports.view',
            ],
            'staff' => [
                'projects.view', 'projects.update',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
            ],
            'finance' => [
                'projects.view',
                'orders.view', 'orders.manage',
                'finance.view', 'finance.manage',
                'reports.view',
            ],
        ];

        foreach (User::ROLES as $slug => $label) {
            $role = Role::findOrCreate($slug, 'web');
            $role->syncPermissions($matrix[$slug] ?? []);
        }

        // Drop the retired Customer / Client role entirely.
        Role::where('name', 'customer')->where('guard_name', 'web')->delete();
    }
}
