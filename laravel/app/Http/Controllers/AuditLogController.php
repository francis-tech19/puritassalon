<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $module = $request->input('module');
        $action = $request->input('action');
        $userId = $request->input('user_id');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $query = AuditLog::with('user');

        if ($module && $module !== 'ALL') {
            $query->where('module', $module);
        }

        if ($action && $action !== 'ALL') {
            $query->where('action', $action);
        }
        if ($userId && $userId !== 'ALL') {
            $query->where('user_id', $userId);
        }
        if ($search) {
            $query->where('details', 'like', '%'.$search.'%');
        }
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $modules = AuditLog::select('module')->distinct()->pluck('module');
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $users = User::whereIn('id', AuditLog::whereNotNull('user_id')->select('user_id')->distinct())
            ->orderBy('name')->get();

        return view('audit.index', compact('logs', 'module', 'action', 'userId', 'search', 'startDate', 'endDate', 'modules', 'actions', 'users'));
    }
}
