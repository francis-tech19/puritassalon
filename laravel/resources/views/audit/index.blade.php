@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Audit Trail & Activity Logs</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Immutable record of all login events, appointment changes, stock movements, and financial checkouts.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div><label class="block text-xs font-black uppercase text-gray-700 mb-1">Module</label>
            <select name="module" class="form-select text-sm font-bold">
                <option value="ALL">All System Modules</option>
                @foreach($modules as $m)
                    <option value="{{ $m }}" {{ $module === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select></div>
            <div><label class="block text-xs font-black uppercase text-gray-700 mb-1">Action</label>
            <select name="action" class="form-select text-sm font-bold"><option value="ALL">All Actions</option>@foreach($actions as $item)<option value="{{ $item }}" {{ $action === $item ? 'selected' : '' }}>{{ $item }}</option>@endforeach</select></div>
            <div><label class="block text-xs font-black uppercase text-gray-700 mb-1">User</label>
            <select name="user_id" class="form-select text-sm font-bold"><option value="ALL">All Users</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ (string) $userId === (string) $user->id ? 'selected' : '' }}>{{ $user->name ?? $user->username }}</option>@endforeach</select></div>
            <div><label class="block text-xs font-black uppercase text-gray-700 mb-1">From</label><input type="date" name="start_date" value="{{ $startDate }}" class="form-input text-sm font-bold"></div>
            <div><label class="block text-xs font-black uppercase text-gray-700 mb-1">To</label><input type="date" name="end_date" value="{{ $endDate }}" class="form-input text-sm font-bold"></div>
            <div><label class="block text-xs font-black uppercase text-gray-700 mb-1">Details</label><input type="search" name="search" value="{{ $search }}" placeholder="Search activity" class="form-input text-sm font-bold"></div>
            <div class="sm:col-span-2 lg:col-span-6 flex gap-2"><button type="submit" class="btn btn-primary text-sm">Apply Filters</button><a href="{{ route('audit.index') }}" class="btn btn-secondary text-sm">Clear</a></div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b-2 border-gray-200">
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Module</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Activity Details</th>
                        <th class="py-3 px-4 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-semibold">
                    @forelse($logs as $log)
                        <tr class="hover:bg-pink-50/40">
                            <td class="py-3.5 px-4 whitespace-nowrap text-gray-600 font-mono text-xs">
                                {{ $log->created_at->format('M d, Y h:i:s A') }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-gray-900">
                                {{ $log->user->name ?? $log->user->username ?? 'Guest/System' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-md text-xs font-black bg-gray-100 text-gray-800 border">
                                    {{ $log->module }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-[#7A1C49]">{{ $log->action }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-800 max-w-md">
                                {{ $log->details }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-xs text-gray-500">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-400 font-bold">
                                No audit log events found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
