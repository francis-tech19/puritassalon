<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'employee'])->orderBy('id')->get();
        $roles = Role::all();
        $employees = Employee::where('status', 'ACTIVE')->get();

        // System Diagnostic Metrics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $totalAuditLogs = AuditLog::count();
        $recentLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->take(8)->get();

        $dbConnection = config('database.default');

        return view('admin.index', compact(
            'users',
            'roles',
            'employees',
            'totalUsers',
            'activeUsers',
            'totalAuditLogs',
            'recentLogs',
            'dbConnection'
        ));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username|max:50',
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'password' => 'required|string|min:10',
            'role_id' => 'required|exists:roles,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'employee_id' => $validated['employee_id'] ?? null,
            'is_active' => true,
        ]);

        AuditLog::log(Auth::id(), 'CREATE_USER', 'ADMIN', "Created user account {$user->username} with Role ID #{$user->role_id}");

        return back()->with('success', "User {$user->username} created successfully!");
    }

    public function toggleUserStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own administrative account.']);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $action = $user->is_active ? 'ACTIVATED' : 'DEACTIVATED';
        AuditLog::log(Auth::id(), 'USER_STATUS', 'ADMIN', "{$action} user account {$user->username}");

        return back()->with('success', "User {$user->username} has been {$action}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:10',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        AuditLog::log(Auth::id(), 'RESET_PASSWORD', 'ADMIN', "Reset password for user account {$user->username}");

        return back()->with('success', "Password for {$user->username} has been reset successfully.");
    }
}
