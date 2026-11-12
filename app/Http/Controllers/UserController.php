<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($validated['search'] ?? '');

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");

                if (in_array(strtolower($search), ['active', 'inactive'], true)) {
                    $query->orWhere('is_active', strtolower($search) === 'active');
                }
            });
        }

        $users = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF, User::ROLE_STUDENT])],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->is($request->user()) && ($validated['role'] !== User::ROLE_ADMIN || ! $request->boolean('is_active'))) {
            return back()->withErrors(['user' => 'You cannot remove your own administrator access or deactivate your account.']);
        }

        $user->forceFill([
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ])->save();

        return back()->with('status', 'User access updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account from User Management.']);
        }

        $user->delete();

        return back()->with('status', 'User deleted successfully.');
    }
}
