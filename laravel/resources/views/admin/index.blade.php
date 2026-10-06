@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ newUserModal: false, resetModal: false, selectedUser: null }">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold role-themed-text tracking-tight flex items-center gap-2">
                <i data-lucide="shield" class="w-8 h-8 text-indigo-800"></i> Administrator Console
            </h1>
            <p class="text-gray-600 font-semibold mt-0.5">Manage operator login accounts, access roles, passwords, and system health status.</p>
        </div>
        <button @click="newUserModal = true" class="btn bg-indigo-900 text-white hover:bg-indigo-950 shadow-md">
            <i data-lucide="user-plus" class="w-5 h-5"></i> Create User Account
        </button>
    </div>

    <!-- Diagnostic Health KPI Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="card p-5 border-l-4 border-l-emerald-600">
            <span class="text-xs font-black uppercase text-gray-500">Database Connection</span>
            <div class="text-xl font-black text-emerald-700 mt-1 uppercase">{{ $dbConnection }} Connected</div>
        </div>

        <div class="card p-5 border-l-4 border-l-indigo-600">
            <span class="text-xs font-black uppercase text-gray-500">Active Operators</span>
            <div class="text-2xl font-black text-indigo-950 mt-1">{{ $activeUsers }} / {{ $totalUsers }}</div>
        </div>

        <div class="card p-5 border-l-4 border-l-purple-600">
            <span class="text-xs font-black uppercase text-gray-500">Total Audit Logs</span>
            <div class="text-2xl font-black text-purple-900 mt-1">{{ $totalAuditLogs }} Events</div>
        </div>

        <div class="card p-5 border-l-4 border-l-amber-600">
            <span class="text-xs font-black uppercase text-gray-500">Framework Engine</span>
            <div class="text-xl font-black text-gray-800 mt-1">Laravel 12</div>
        </div>
    </div>

    <!-- Access Control & Audit Activity -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Access Control Summary -->
        <div class="card p-6 border-l-4 border-indigo-700">
            <h3 class="text-xl font-extrabold text-indigo-950 flex items-center gap-2 mb-3">
                <i data-lucide="key-round" class="w-5 h-5"></i> Access Control Summary
            </h3>
            <p class="text-sm text-gray-600 font-semibold mb-4">Review account distribution and keep access limited to active operators.</p>
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-3">
                    <div class="text-xs font-black uppercase text-indigo-700">Admins</div>
                    <div class="text-2xl font-black text-indigo-950 mt-1">{{ $users->filter(fn ($user) => $user->isAdmin())->count() }}</div>
                </div>
                <div class="rounded-xl bg-pink-50 border border-pink-100 p-3">
                    <div class="text-xs font-black uppercase text-[#7A1C49]">Owners</div>
                    <div class="text-2xl font-black text-[#7A1C49] mt-1">{{ $users->filter(fn ($user) => $user->isOwner())->count() }}</div>
                </div>
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-3">
                    <div class="text-xs font-black uppercase text-emerald-700">Staff</div>
                    <div class="text-2xl font-black text-emerald-900 mt-1">{{ $users->filter(fn ($user) => !$user->isAdmin() && !$user->isOwner())->count() }}</div>
                </div>
            </div>
        </div>

        <!-- Recent Audit Activity -->
        <div class="card p-6 border-l-4 border-amber-600 self-start">
            <h3 class="text-xl font-extrabold text-amber-800 flex items-center gap-2 mb-3">
                <i data-lucide="activity" class="w-5 h-5"></i> Recent Audit Activity
            </h3>
            <div class="divide-y divide-gray-100 max-h-48 overflow-y-auto">
                @forelse($recentLogs->take(4) as $log)
                    <div class="py-2 first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-black uppercase text-indigo-700">{{ $log->action }}</span>
                            <span class="text-xs font-semibold text-gray-400 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-700 truncate">{{ $log->details ?? 'System activity recorded' }}</p>
                        <p class="text-xs text-gray-500">{{ $log->user->username ?? 'System' }} · {{ $log->module }}</p>
                    </div>
                @empty
                    <p class="text-sm font-semibold text-gray-500 py-4">No audit activity has been recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- User Accounts Table -->
    <div class="card p-0 overflow-hidden border-2 border-indigo-100">
        <div class="p-5 border-b-2 border-gray-100 flex items-center justify-between">
            <h3 class="text-xl font-extrabold text-indigo-950 flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5"></i> System Login Accounts
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b">
                        <th class="py-3 px-4">Username & Name</th>
                        <th class="py-3 px-4">Role Permission</th>
                        <th class="py-3 px-4">Linked Employee</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Account Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-semibold">
                    @foreach($users as $u)
                        <tr class="hover:bg-indigo-50/30">
                            <td class="py-4 px-4">
                                <div class="font-black text-gray-900 text-base">{{ $u->username }}</div>
                                <div class="text-xs text-gray-500">{{ $u->name }} ({{ $u->email ?? 'No email' }})</div>
                            </td>

                            <td class="py-4 px-4">
                                @if($u->isAdmin())
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-indigo-900 text-white">ADMIN</span>
                                @elseif($u->isOwner())
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-[#7A1C49] text-white">OWNER</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">STAFF</span>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-gray-700">
                                {{ $u->employee->full_name ?? 'Not Linked' }}
                            </td>

                            <td class="py-4 px-4 text-center">
                                @if($u->is_active)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">ACTIVE</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-red-100 text-red-800">DEACTIVATED</span>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Reset Password -->
                                    <button type="button" @click="selectedUser = {{ json_encode($u) }}; resetModal = true"
                                            class="px-2.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 text-xs font-bold text-gray-700">
                                        Reset Pass
                                    </button>

                                    <!-- Toggle Status -->
                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle', $u->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" onclick="return confirm('Change status of {{ $u->username }}?')"
                                                    class="px-2.5 py-1.5 rounded-lg text-xs font-black border transition
                                                    {{ $u->is_active ? 'border-red-300 text-red-700 hover:bg-red-50' : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' }}">
                                                {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create User Modal -->
    <div x-show="newUserModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" style="display: none;" role="dialog" aria-modal="true">
        <div @click="newUserModal = false" class="fixed inset-0 modal-backdrop transition-opacity"></div>

        <div class="relative z-10 bg-white rounded-3xl text-left shadow-2xl w-full max-w-lg border-2 border-gray-200 p-6 sm:p-8 max-h-[90vh] overflow-y-auto my-auto">
            <div class="flex items-center justify-between pb-3 mb-4 border-b">
                <h3 class="text-2xl font-extrabold text-indigo-950">Create Operator Account</h3>
                <button @click="newUserModal = false" class="text-gray-400 hover:text-gray-700"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Username</label>
                        <input type="text" name="username" required placeholder="e.g. stylist_maria" class="form-input font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Full Name</label>
                        <input type="text" name="name" required placeholder="Maria Dela Cruz" class="form-input font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Role</label>
                        <select name="role_id" required class="form-select font-bold">
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Linked Employee</label>
                        <select name="employee_id" class="form-select font-bold">
                            <option value="">None / Admin</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Email (Optional)</label>
                    <input type="email" name="email" placeholder="operator@beauty.com" class="form-input font-bold">
                </div>

                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Initial Password</label>
                    <input type="password" name="password" required minlength="10" placeholder="Minimum 10 characters" class="form-input font-bold">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" @click="newUserModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn bg-indigo-900 text-white hover:bg-indigo-950">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="resetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" style="display: none;" role="dialog" aria-modal="true">
        <div @click="resetModal = false" class="fixed inset-0 modal-backdrop transition-opacity"></div>

        <div class="relative z-10 bg-white rounded-3xl text-left shadow-2xl w-full max-w-md border-2 border-gray-200 p-6 sm:p-8 max-h-[90vh] overflow-y-auto my-auto">
            <div class="flex items-center justify-between pb-3 mb-4 border-b">
                <h3 class="text-xl font-extrabold text-indigo-950">Reset Password</h3>
                <button @click="resetModal = false" class="text-gray-400 hover:text-gray-700"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>

            <template x-if="selectedUser">
                <form :action="'/admin/users/' + selectedUser.id + '/reset-password'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <span class="text-xs font-bold uppercase text-gray-500">Account:</span>
                        <div class="font-extrabold text-base text-gray-900" x-text="selectedUser.username + ' (' + selectedUser.name + ')'"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">New Password</label>
                        <input type="password" name="password" required minlength="10" placeholder="Enter new password" class="form-input font-bold">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button type="button" @click="resetModal = false" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn bg-indigo-900 text-white hover:bg-indigo-950">Update Password</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
