<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RoleSimulationController extends Controller
{
    /**
     * Preview another role by storing it in the session.
     * Restricted to the owner. The real role is NEVER changed in the database.
     */
    public function update(Request $request)
    {
        $this->authorizeOwner($request);

        $data = $request->validate([
            'role' => ['required', 'string', 'in:'.implode(',', array_keys(User::ROLES))],
        ]);

        // Remember the real role the first time (it stays super-admin).
        if (! $request->session()->has('original_role')) {
            $request->session()->put('original_role', $request->user()->getRoleNames()->first());
        }

        if ($data['role'] === $request->session()->get('original_role')) {
            $request->session()->forget('simulated_role');

            return back()->with('status', 'Back to your real role.');
        }

        $request->session()->put('simulated_role', $data['role']);

        return back()->with('status', 'Now previewing as '.User::ROLES[$data['role']].'. Your real role is unchanged.');
    }

    /** Stop simulating and return to the real role. */
    public function reset(Request $request)
    {
        $this->authorizeOwner($request);

        $request->session()->forget(['simulated_role', 'original_role']);

        return back()->with('status', 'Role simulation cleared.');
    }

    /**
     * Allow the request only if the user is REALLY the owner — i.e. their
     * stored (original) role is super-admin. Because the session stores the
     * real role separately, this still succeeds while simulating a lesser role,
     * which is what makes switching back possible.
     */
    private function authorizeOwner(Request $request): void
    {
        $realRole = $request->session()->get('original_role');
        $user = $request->user();

        $isOwner = $realRole === 'super-admin'
            || ($realRole === null && $user->hasRole('super-admin'));

        abort_unless($isOwner, 403);
    }
}
