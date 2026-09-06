<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin user management + role assignment. Gate all routes with
 * ['auth', 'admin'] middleware — this controller assumes only admins reach it.
 */
class AdminUserController extends Controller
{
    private const VALID_ROLES = ['user', 'admin'];

    public function index(): View
    {
        // name/email decrypt automatically on retrieval (EncryptableFields),
        // so this list shows plaintext without any extra work here.
        $users = User::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:' . implode(',', self::VALID_ROLES),
        ]);

        // Don't allow the last admin to demote themselves (or be demoted) —
        // that would lock everyone out of /admin/* permanently.
        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            $remainingAdmins = User::where('role', 'admin')->where('id', '!=', $user->id)->count();

            if ($remainingAdmins === 0) {
                return back()->with('error', 'Cannot remove the last remaining admin.');
            }
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('status', "Updated {$user->name}'s role to {$validated['role']}.");
    }
}
