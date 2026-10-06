<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::user();
        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('login')->withErrors(['username' => 'Account is inactive.']);
        }

        $userRole = strtoupper($user->role->role_name ?? '');

        // Normalize roles to uppercase
        $allowedRoles = array_map('strtoupper', $roles);

        if (empty($allowedRoles) || in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this resource.',
            ], 403);
        }

        // Redirect based on what role the user actually has
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Unauthorized access for this module.');
        }
        if ($user->isOwner()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access for this module.');
        }
        if ($user->isCustomer()) {
            return redirect()->route('customer.dashboard')->with('error', 'Unauthorized access for this module.');
        }

        return redirect()->route('appointments.index')->with('error', 'Unauthorized access for this module.');
    }
}
