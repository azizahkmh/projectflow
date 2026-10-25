<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * "Don't have an account? Request access" flow — the brief only asks for the
 * link, so this records the request in the activity log for the owner to review.
 */
class AccessRequestController extends Controller
{
    public function create()
    {
        return view('auth.request-access');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        \App\Models\Activity::create([
            'user_id' => null,
            'action' => 'access_requested',
            'description' => "Access requested by {$data['name']} ({$data['email']})",
            'icon' => 'user-plus',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('login')
            ->with('status', 'Thanks! Your access request was received — an administrator will reach out shortly.');
    }
}
