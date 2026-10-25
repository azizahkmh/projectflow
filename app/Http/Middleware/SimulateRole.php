<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets the owner "preview" another role without ever touching the database.
 *
 * The chosen role is kept in the session. For the duration of the request we
 * swap the in-memory `roles` relation, so hasRole() / can() / getRoleNames()
 * all see the simulated role — but the user's real role is never rewritten.
 * Clearing the session (or logging out) instantly restores the real role.
 */
class SimulateRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $simulated = $request->session()->get('simulated_role');

        if ($user && is_string($simulated) && array_key_exists($simulated, User::ROLES)) {
            // Only the real owner may simulate, and only while the real role
            // is still super-admin. Guard against a stranger forging the key.
            $realRole = $request->session()->get('original_role');

            if ($realRole === 'super-admin' && $user->hasRole('super-admin')) {
                $roleModel = \Spatie\Permission\Models\Role::findByName($simulated, 'web');

                if ($roleModel) {
                    // Override ONLY the in-memory relation for this request.
                    $user->setRelation('roles', collect([$roleModel]));

                    // Permission caches key off the relation; clear them.
                    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                    $user->unsetRelation('permissions');
                }
            }
        }

        return $next($request);
    }
}
